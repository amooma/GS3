#!/bin/bash
#
# Migrate a Gemeinschaft 3.2 system (Asterisk 1.8) to a freshly installed
# Gemeinschaft system on Ubuntu 26.04 (Asterisk 22, MariaDB).
#
# This is a separate, manual task. It is not part of install.sh.
# Run it as root ON THE NEW HOST, after a normal fresh install there.
#
# The source data is only read. All changes are made on the new host, so
# the old system stays ready to be started again (rollback).
#
# Usage:
#   migrate-to-asterisk22.sh check         --source-ssh "root@old-host"
#   migrate-to-asterisk22.sh run           --source-ssh "root@old-host"
#   migrate-to-asterisk22.sh verify        --source-ssh "root@old-host"
#
# The default target is the local MariaDB of the new host.
#
# If the old database is on a shared server, the new host uses that server
# too. Then the old database has to make room for the new one:
#   migrate-to-asterisk22.sh rename-old    --db-args "-h dbhost -u admin -p..." --old-name asterisk_old
#   migrate-to-asterisk22.sh run           --source-ssh "root@old-host" --shared --old-name asterisk_old --db-args "..."
#   migrate-to-asterisk22.sh verify        --source-ssh "root@old-host" --shared --old-name asterisk_old --db-args "..."
# and for a rollback:
#   migrate-to-asterisk22.sh undo-rename   --db-args "..." --old-name asterisk_old
#
# Options:
#   --source-ssh "ARGS"  ssh arguments for the old host, e.g. "root@10.0.0.5 -p 22"
#   --source-db NAME     source database on the old host (default: asterisk)
#   --shared             the old database is on the same server as the new one
#   --old-name NAME      name of the renamed old database (with --shared)
#   --db-args "ARGS"     mysql client arguments for the database server of the new host
#   --ignore-extra       continue although the source has tables unknown to Gemeinschaft
#
# Procedure:
#   1. Fresh install on the new host, on a temporary IP address.
#   2. "check", then "run" as a rehearsal while the old system keeps running.
#   3. Downtime: stop Asterisk and cron on the old host.
#   4. "run" again for the final copy. "run" always starts from an empty target.
#   5. Test the new host. Check the configuration differences printed by "run".
#   6. Take the old host off the network, give the new host its IP and reboot it.
#
# Rollback: take the new host off that IP and start the old host again.

set -u
set -o pipefail

TARGET_DB=asterisk
SOURCE_SSH=""
SOURCE_DB=asterisk
SHARED=0
OLD_NAME=""
DB_ARGS=""
IGNORE_EXTRA=0
WORK=/var/tmp/gs-migrate
SQL_SCHEMA=/opt/gemeinschaft-source/usr/share/doc/gemeinschaft/asterisk.sql
CFG=/etc/gemeinschaft/gemeinschaft.php

# tables whose content is changed after the copy, or which hold runtime state
CHANGED_TABLES="ast_queue_members gates gate_params conferences call_completion_waiting"
CARD_TYPES="'zap','misdn','dahdi','capi','woomera','vpb'"
# data on disk that belongs to the users (copied, never moved)
DATA_PATHS="var/spool/asterisk/voicemail opt/gemeinschaft/vm-rec opt/gemeinschaft/htdocs/prov/ringtones var/spool/hylafax/recvq var/spool/hylafax/doneq var/spool/hylafax/docq var/spool/hylafax/info var/spool/hylafax/archive"

die() { echo "ERROR: $*" >&2; exit 1; }
say() { echo "*** $*"; }

COMMAND="${1:-}"; [ $# -gt 0 ] && shift
while [ $# -gt 0 ]; do
	case "$1" in
		--source-ssh)   SOURCE_SSH="$2"; shift 2;;
		--source-db)    SOURCE_DB="$2"; shift 2;;
		--shared)       SHARED=1; shift;;
		--old-name)     OLD_NAME="$2"; shift 2;;
		--db-args)      DB_ARGS="$2"; shift 2;;
		--ignore-extra) IGNORE_EXTRA=1; shift;;
		*) die "unknown option: $1";;
	esac
done

[ "$(id -u)" = 0 ] || die "run this as root on the new host"

# shellcheck disable=SC2086
t_sql()  { mysql $DB_ARGS --default-character-set=utf8mb4 -A -N -B "$@"; }

# Read-only access to the source database.
src_sql() {
	if [ "$SHARED" = 1 ]; then
		t_sql "$OLD_NAME" -e "$1"
	else
		# shellcheck disable=SC2086
		printf '%s\n' "$1" | ssh -o BatchMode=yes $SOURCE_SSH "mysql --default-character-set=utf8mb4 -A -N -B $SOURCE_DB"
	fi
}
src_db() { if [ "$SHARED" = 1 ]; then echo "$OLD_NAME"; else echo "$SOURCE_DB"; fi; }
# shellcheck disable=SC2086
src_ssh() { ssh -n -o BatchMode=yes $SOURCE_SSH "$@"; }

need_source() {
	[ -n "$SOURCE_SSH" ] || die "--source-ssh is required"
	if [ "$SHARED" = 1 ]; then [ -n "$OLD_NAME" ] || die "--shared needs --old-name"; fi
}

base_tables() { "$1" "SELECT table_name FROM information_schema.tables WHERE table_schema=$2 AND table_type='BASE TABLE' ORDER BY 1"; }
src_tables()  { src_sql "SELECT table_name FROM information_schema.tables WHERE table_schema='$(src_db)' AND table_type='BASE TABLE' ORDER BY 1"; }
tgt_tables()  { t_sql -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$TARGET_DB' AND table_type='BASE TABLE' ORDER BY 1"; }
src_columns() { src_sql "SELECT column_name FROM information_schema.columns WHERE table_schema='$(src_db)' AND table_name='$1' ORDER BY ordinal_position"; }
tgt_columns() { t_sql -e "SELECT column_name FROM information_schema.columns WHERE table_schema='$TARGET_DB' AND table_name='$1' ORDER BY ordinal_position"; }

# ---- check ---------------------------------------------------------
cmd_check() {
	need_source
	say "Source: $(src_sql 'SELECT VERSION()')  database: $(src_db)"
	say "Target: $(t_sql -e 'SELECT VERSION()')  database: $TARGET_DB"
	local stop=0 src tgt extra n
	src=$(src_tables); tgt=$(tgt_tables)
	[ -n "$src" ] || die "no tables found in the source database"
	[ -n "$tgt" ] || die "the target database has no tables. Is the fresh install done?"
	extra=$(comm -23 <(echo "$src" | sort) <(echo "$tgt" | sort))
	if [ -n "$extra" ]; then
		echo "Tables that exist only in the source:"; echo "$extra" | sed 's/^/    /'
		[ "$IGNORE_EXTRA" = 1 ] && echo "    (ignored with --ignore-extra, they are not copied)" || stop=1
	fi
	n=$(src_sql "SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema='$(src_db)'")
	[ "$n" = 0 ] || { echo "The source has $n trigger(s), which Gemeinschaft does not define."; stop=1; }
	n=$(src_sql "SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema='$(src_db)'")
	[ "$n" = 0 ] || { echo "The source has $n stored routine(s), which Gemeinschaft does not define."; stop=1; }
	local t c missing
	for t in $(comm -12 <(echo "$src" | sort) <(echo "$tgt" | sort)); do
		missing=$(comm -23 <(src_columns "$t" | sort) <(tgt_columns "$t" | sort))
		if [ -n "$missing" ]; then
			for c in $missing; do echo "Column $t.$c exists only in the source."; done
			stop=1
		fi
	done
	say "Source tables: $(echo "$src" | wc -l), engines: $(src_sql "SELECT GROUP_CONCAT(CONCAT(e,':',c)) FROM (SELECT engine e,COUNT(*) c FROM information_schema.tables WHERE table_schema='$(src_db)' AND table_type='BASE TABLE' GROUP BY engine) x")"
	say "CDRs: $(src_sql 'SELECT COUNT(*) FROM ast_cdr')  queue_log: $(src_sql 'SELECT COUNT(*) FROM queue_log')"
	[ "$stop" = 0 ] || die "the source has unexpected content, see above"
	say "Check passed."
}

# ---- fingerprints ---------------------------------------------------
# Same expression on both sides: one row count and one order-independent
# checksum over all columns that both tables have.
hash_expr() {
	local t="$1" cols="$2" where="${3:-1=1}" c out=""
	for c in $cols; do
		out="${out:+$out,}IFNULL(CAST(\`$c\` AS CHAR CHARACTER SET utf8mb4),'<NULL>')"
	done
	echo "SELECT COUNT(*),LOWER(CONV(BIT_XOR(CRC32(CONCAT_WS(0x1F,$out))),10,16)) FROM \`$t\` WHERE $where"
}
common_columns() { comm -12 <(src_columns "$1" | sort) <(tgt_columns "$1" | sort) | tr '\n' ' '; }
src_hash() { src_sql "$(hash_expr "$1" "$(common_columns "$1")" "${2:-1=1}")"; }
tgt_hash() { t_sql "$TARGET_DB" -e "$(hash_expr "$1" "$(common_columns "$1")" "${2:-1=1}")"; }
# Rows that the new system adds after the copy must not count as a difference.
copy_limit() {
	case "$1" in
		ast_cdr)   echo "\`calldate\` <= '$(src_sql 'SELECT MAX(calldate) FROM ast_cdr')'";;
		queue_log) echo "\`timestamp\` <= '$(src_sql 'SELECT MAX(`timestamp`) FROM queue_log')'";;
		*)         echo "1=1";;
	esac
}

source_fingerprint() {
	local t
	for t in $(src_tables); do echo "$t $(src_hash "$t")"; done
}

cmd_verify() {
	need_source
	say "Comparing the source and the new database ..."
	local t s d limit bad=0 checked=0 skipped=0 tables
	tables=$(comm -12 <(src_tables | sort) <(tgt_tables | sort))
	for t in $tables; do
		case " $CHANGED_TABLES " in *" $t "*) skipped=$((skipped+1)); continue;; esac
		limit=$(copy_limit "$t"); s=$(src_hash "$t" "$limit"); d=$(tgt_hash "$t" "$limit")
		checked=$((checked+1))
		if [ "$s" != "$d" ]; then echo "DIFFERENT: $t  source: $s  target: $d"; bad=$((bad+1)); fi
	done
	say "Identical tables: $((checked-bad)) of $checked ($skipped tables are changed on purpose, see the report)"
	local a b
	for t in ast_cdr queue_log; do
		limit=$(copy_limit "$t")
		a=$(src_sql "SELECT COUNT(*) FROM \`$t\` WHERE $limit"); b=$(t_sql "$TARGET_DB" -e "SELECT COUNT(*) FROM \`$t\` WHERE $limit")
		say "$t: source $a rows, target $b rows up to the time of the copy"
	done
	a=$(src_sql "SELECT COUNT(*) FROM ast_queue_members"); b=$(t_sql "$TARGET_DB" -e "SELECT COUNT(*) FROM ast_queue_members")
	say "ast_queue_members: source $a rows, target $b rows"
	[ "$bad" = 0 ] || die "$bad table(s) differ"
	say "Verification passed."
}

# ---- run ------------------------------------------------------------
dump_source() {
	local tables="$1" out="$2" opts="--no-create-info --complete-insert --skip-triggers --single-transaction --quick --hex-blob --default-character-set=utf8mb4 --skip-add-locks --skip-comments"
	if [ "$SHARED" = 1 ]; then
		# shellcheck disable=SC2086
		mysqldump $DB_ARGS $opts "$OLD_NAME" $tables > "$out"
	else
		src_ssh "O=\"$opts\"; mysqldump --help | grep -q set-gtid-purged && O=\"\$O --set-gtid-purged=OFF\"; mysqldump --help | grep -q no-tablespaces && O=\"\$O --no-tablespaces\"; mysqldump \$O $SOURCE_DB $tables" > "$out"
	fi
}

cmd_run() {
	need_source
	cmd_check
	mkdir -p "$WORK"; chmod 700 "$WORK"
	say "Stopping Asterisk and cron on this host for the import ..."
	systemctl stop asterisk cron || true
	source_fingerprint > "$WORK/source.before"

	local tables
	tables=$(comm -12 <(src_tables | sort) <(tgt_tables | sort) | tr '\n' ' ')
	say "Reading the source database ..."
	dump_source "$tables" "$WORK/data.sql" || die "reading the source failed"

	say "Emptying the new database ..."
	{ echo "SET FOREIGN_KEY_CHECKS=0;"; for t in $(tgt_tables); do echo "TRUNCATE TABLE \`$t\`;"; done; } | t_sql "$TARGET_DB" || die "emptying failed"

	say "Importing ..."
	{ echo "SET FOREIGN_KEY_CHECKS=0; SET UNIQUE_CHECKS=0; SET sql_mode='NO_AUTO_VALUE_ON_ZERO';"; cat "$WORK/data.sql"; } | t_sql "$TARGET_DB" || die "import failed"

	say "Adapting the copy to Asterisk 22 ..."
	t_sql "$TARGET_DB" <<EOSQL || die "adapting the data failed"
UPDATE ast_queue_members SET interface=CONCAT('PJSIP/',SUBSTRING(interface,5)) WHERE interface LIKE 'SIP/%';
UPDATE gates SET dialstr=CONCAT('PJSIP/',SUBSTRING(dialstr,5)) WHERE dialstr LIKE 'SIP/%';
DELETE FROM gates WHERE type IN ($CARD_TYPES);
TRUNCATE TABLE conferences;
TRUNCATE TABLE call_completion_waiting;
EOSQL

	cmd_verify

	say "Copying files from the old host ..."
	local p present=""
	for p in $DATA_PATHS; do src_ssh "test -e /$p" && present="$present $p"; done
	# shellcheck disable=SC2086
	src_ssh "tar -C / -cf - $present" | tar -C / -xf - || die "copying files failed"

	say "Importing queue events that were still in the log file ..."
	if src_ssh "test -s /var/log/asterisk/queue_log"; then
		src_ssh "cat /var/log/asterisk/queue_log" > /var/log/asterisk/queue_log.migrated
		chown asterisk:asterisk /var/log/asterisk/queue_log.migrated
	fi

	local ip
	ip=$(src_ssh "sed -nE \"s/^[[:space:]]*\\\$PROV_HOST[[:space:]]*=[[:space:]]*['\\\"]([^'\\\"]*)['\\\"].*/\\1/p\" $CFG" | head -1)
	if [ -n "$ip" ]; then
		say "Setting PROV_HOST to the IP address of the old host: $ip"
		sed -i -E "s/^([[:space:]]*\\\$PROV_HOST[[:space:]]*=[[:space:]]*)['\"][^'\"]*['\"]/\\1'$ip'/" "$CFG"
	fi

	/opt/gemeinschaft/sbin/gs-queuelog-to-db >/dev/null 2>&1 || true
	/opt/gemeinschaft/sbin/gs-hylafax-auth-update >/dev/null 2>&1 || true
	systemctl start asterisk cron || true
	/opt/gemeinschaft/sbin/start-asterisk --dialplan >/dev/null 2>&1 || true

	source_fingerprint > "$WORK/source.after"
	cmp -s "$WORK/source.before" "$WORK/source.after" \
		|| echo "WARNING: the source data changed during the copy. Is the old PBX still running? This is expected in a rehearsal."

	say "Report"
	echo "Gateways removed (ISDN/analogue): $(src_sql "SELECT COUNT(*) FROM gates WHERE type IN ($CARD_TYPES)")"
	echo "Queue members now on PJSIP: $(t_sql "$TARGET_DB" -e "SELECT COUNT(*) FROM ast_queue_members WHERE interface LIKE 'PJSIP/%'")"
	echo "Gateway groups used by routes but without any gateway:"
	t_sql "$TARGET_DB" -e "SELECT DISTINCT g.name FROM routes r JOIN gate_grps g ON g.id IN (r.gw_grp_id_1,r.gw_grp_id_2,r.gw_grp_id_3) WHERE r.active=1 AND NOT EXISTS (SELECT 1 FROM gates x WHERE x.grp_id=g.id)" | sed 's/^/    /'
	echo "Configuration differences to the old host (review by hand, nothing was merged):"
	src_ssh "grep -vE '^[[:space:]]*(#|//|\$)' $CFG" | grep -vE 'DB_(MASTER|SLAVE)_PWD' | sort > "$WORK/cfg.old"
	grep -vE '^[[:space:]]*(#|//|$)' "$CFG" | grep -vE 'DB_(MASTER|SLAVE)_PWD' | sort > "$WORK/cfg.new"
	diff "$WORK/cfg.old" "$WORK/cfg.new" | grep -E '^[<>]' | cut -c1-120 | sed 's/^/    /' | head -40
	rm -f "$WORK/data.sql"
	say "Done. Test this system before moving the IP address."
}

# ---- rename the old database on a shared server -----------------------
# Only names change, not the rows. MySQL and MariaDB have no RENAME DATABASE,
# so every table is moved. Views cannot be moved and are recreated.
cmd_rename_old() {
	[ -n "$OLD_NAME" ] || die "--old-name is required"
	[ "$OLD_NAME" != "$TARGET_DB" ] || die "--old-name must differ from $TARGET_DB"
	local n
	n=$(t_sql -e "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='$OLD_NAME'")
	[ "$n" = 0 ] || die "database $OLD_NAME already exists"
	[ "$(t_sql -e "SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema='$TARGET_DB'")" = 0 ] || die "the old database has triggers, which cannot be moved"
	say "Moving the tables of $TARGET_DB to $OLD_NAME ..."
	t_sql -e "CREATE DATABASE \`$OLD_NAME\` CHARACTER SET utf8" || die "cannot create $OLD_NAME"
	move_tables "$TARGET_DB" "$OLD_NAME"
	t_sql -e "DROP DATABASE \`$TARGET_DB\`" || die "$TARGET_DB is not empty, nothing further was changed"
	say "Creating the new, empty $TARGET_DB ..."
	[ -f "$SQL_SCHEMA" ] || die "$SQL_SCHEMA not found"
	sed -e 's/DEFINER *= *[^ ]*/DEFINER=CURRENT_USER()/g' "$SQL_SCHEMA" | t_sql || die "creating the new database failed"
	say "Done. The old database is now $OLD_NAME. Continue with: run --shared --old-name $OLD_NAME"
}

cmd_undo_rename() {
	[ -n "$OLD_NAME" ] || die "--old-name is required"
	[ "$(t_sql -e "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='$OLD_NAME'")" = 1 ] || die "database $OLD_NAME not found"
	say "Removing the new $TARGET_DB and moving $OLD_NAME back ..."
	t_sql -e "DROP DATABASE IF EXISTS \`$TARGET_DB\`; CREATE DATABASE \`$TARGET_DB\` CHARACTER SET utf8" || die "cannot recreate $TARGET_DB"
	move_tables "$OLD_NAME" "$TARGET_DB"
	t_sql -e "DROP DATABASE \`$OLD_NAME\`" || die "$OLD_NAME is not empty"
	say "Done. The old database is $TARGET_DB again."
}

# The views are recreated after the tables have moved, while the originals
# still exist, and are only dropped once all of them work in the new place.
move_tables() {
	local from="$1" to="$2" t v def list="" left pass made
	mkdir -p "$WORK"; chmod 700 "$WORK"
	: > "$WORK/views.$from"
	for v in $(t_sql -e "SELECT table_name FROM information_schema.views WHERE table_schema='$from' ORDER BY 1"); do
		def=$(t_sql -e "SHOW CREATE VIEW \`$from\`.\`$v\`" | cut -f2)
		def=$(printf '%s' "$def" | sed -e "s/\`$from\`\./\`$to\`./g" -e 's/DEFINER=`[^`]*`@`[^`]*` //')
		printf '%s\n' "$def" > "$WORK/view.$v"
		printf '%s;\n' "$def" >> "$WORK/views.$from"
		list="$list $v"
	done
	for t in $(t_sql -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$from' AND table_type='BASE TABLE'"); do
		t_sql -e "RENAME TABLE \`$from\`.\`$t\` TO \`$to\`.\`$t\`" || die "cannot move table $t. Run undo-rename to move the tables back."
	done
	# views may depend on each other, so retry until all exist
	left="$list"
	for pass in 1 2 3 4 5; do
		[ -n "$(echo "$left" | tr -d ' ')" ] || break
		made=""
		for v in $left; do
			if t_sql "$to" < "$WORK/view.$v" 2>/dev/null; then made="$made $v"; fi
		done
		for v in $made; do left=$(echo " $left " | sed "s/ $v / /"); done
	done
	[ -z "$(echo "$left" | tr -d ' ')" ] || die "cannot recreate the views:$left. Their definitions are in $WORK/views.$from, the tables are in $to. Run undo-rename."
	for v in $list; do
		t_sql -e "DROP VIEW \`$from\`.\`$v\`" || die "cannot drop view $v"
	done
	rm -f "$WORK"/view.*
}

case "$COMMAND" in
	check)        cmd_check;;
	run)          cmd_run;;
	verify)       cmd_verify;;
	rename-old)   cmd_rename_old;;
	undo-rename)  cmd_undo_rename;;
	*) sed -n '2,/^set -u/p' "$0" | sed -e '/^set -u/d' -e 's/^# \{0,1\}//'; exit 1;;
esac
