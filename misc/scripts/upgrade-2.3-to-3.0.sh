#!/bin/bash -x

which mysql || echo "No binary"

echo "What is your MYSQL root password?"
read n
MY_MYSQL_PASS="$n";


MY_MYSQL="mysql -N -p$MY_MYSQL_PASS " 

$MY_MYSQL -e "CREATE DATABASE gemeinschaft_schema;"
$MY_MYSQL -D gemeinschaft_schema -e "create view old as select * from information_schema.columns where table_schema='asterisk';" 
$MY_MYSQL -D gemeinschaft_schema -e "create view new as select * from information_schema.columns where table_schema='asterisk_new';"
test -f ./asterisk_new.mysql || exit 1
$MY_MYSQL -e "CREATE DATABASE asterisk_new" || exit 1
$MY_MYSQL -D asterisk_new < asterisk_new.mysql

MY_CHANGES="select new.TABLE_NAME, new.COLUMN_NAME, new.COLUMN_DEFAULT, new.IS_NULLABLE, new.DATA_TYPE, new.COLUMN_TYPE, new.EXTRA, new.COLUMN_KEY from new left join old on new.column_name=old.column_name and new.table_name=old.table_name where old.column_name is null and new.table_name in (select distinct(table_name) from old) and new.TABLE_NAME !='ast_sipfriends_gs';"

$MY_MYSQL --batch -D gemeinschaft_schema  -e "$MY_CHANGES" | tr '\t' '|' |  while IFS='|' read -a arr 
do 
	#arr=(${line//	/ })
	#arr=($line);
	TABLE=${arr[0]};
	COLUMN=${arr[1]};
	DEFAULT="default ${arr[2]}";
	NULLABLE=${arr[3]};
	DATATYPE=${arr[4]};
	COLUMNTYPE=${arr[5]};
	EXTRA=${arr[6]};
	KEY=${arr[7]};

	case $NULLABLE in NO)
		NULLABLE="NOT NULL";
		if [ "$DEFAULT" = "default NULL" ]
		then
		       DEFAULT="";
	       fi
		;;
	*)
		NULLABLE="";
		;;
	esac

	case $KEY in PRI)
		MY_CHANGE="alter table \`$TABLE\` add COLUMN \`$COLUMN\` $COLUMNTYPE $EXTRA $NULLABLE $DEFAULT PRIMARY KEY;"
		MY_KEY="(";
		MY_NAME="";
		for i in $($MY_MYSQL -N -D gemeinschaft_schema -e "SELECT COLUMN_NAME from old WHERE \`COLUMN_KEY\` = 'PRI' AND \`TABLE_NAME\` = '$TABLE';")
			
		do
			MY_KEY="$MY_KEY\`$i\`,";
			MYNAME=$MYNAME\_$i;
		done
		MY_KEY=$(echo $MY_KEY | sed -e 's/,$/\)/');
		MY_NAME=$(echo $MY_NAME | sed -e 's/^_//');
		$MY_MYSQL -D asterisk -e "ALTER TABLE $TABLE DROP PRIMARY KEY;";
		$MY_MYSQL -D asterisk -e "ALTER TABLE $TABLE add unique key \`$MYNAME\` $MY_KEY;";
		;;
	*)
	MY_CHANGE="alter table \`$TABLE\` add COLUMN \`$COLUMN\` $COLUMNTYPE $EXTRA $NULLABLE $DEFAULT;"
	;;
	esac
	$MY_MYSQL -D asterisk -e "$MY_CHANGE";
done;

MY_MISSING="select distinct(new.table_name) from new left join old on old.table_name=new.table_name where old.table_name is null;"

for i in $($MY_MYSQL -D gemeinschaft_schema  -e "$MY_MISSING");
	do
		$MY_MYSQL -D asterisk -e "create table $i like asterisk_new.$i;";
		$MY_MYSQL -D asterisk -e "INSERT INTO $i (SELECT * FROM asterisk_new.$i);";
	done
MY_VIEW="DROP TABLE IF EXISTS \`ast_sipfriends_gs\`;
/*!50001 DROP VIEW IF EXISTS \`ast_sipfriends_gs\`*/;
/*!50001 CREATE TABLE \`ast_sipfriends_gs\` (
  \`_user_id\` int(10) unsigned,
  \`name\` varchar(16),
  \`secret\` varchar(16),
  \`type\` enum('friend','user','peer'),
  \`host\` varchar(50),
  \`defaultip\` varchar(15),
  \`context\` varchar(50),
  \`callerid\` varchar(80),
  \`mailbox\` varchar(25),
  \`callgroup\` varchar(20),
  \`pickupgroup\` varchar(20),
  \`setvar\` varchar(50),
  \`call-limit\` tinyint(3) unsigned,
  \`subscribecontext\` varchar(50),
  \`regcontext\` varchar(50),
  \`ipaddr\` varchar(15),
  \`port\` varchar(5),
  \`regseconds\` bigint(20),
  \`username\` varchar(25),
  \`regserver\` varchar(50),
  \`fullcontact\` varchar(100),
  \`accountcode\` varchar(20),
  \`allowtransfer\` varchar(20),
  \`allow\` varchar(20),
  \`amaflags\` varchar(20),
  \`auth\` varchar(10),
  \`autoframing\` varchar(10),
  \`callingpres\` varchar(20),
  \`cid_number\` varchar(40),
  \`defaultuser\` varchar(40),
  \`fromdomain\` varchar(40),
  \`fromuser\` varchar(40),
  \`incominglimit\` varchar(10),
  \`insecure\` varchar(20),
  \`language\` varchar(10),
  \`lastms\` int(11),
  \`maxcallbitrate\` varchar(15),
  \`md5secret\` varchar(40),
  \`mohsuggest\` varchar(20),
  \`musicclass\` varchar(20),
  \`outboundproxy\` varchar(40),
  \`qualify\` varchar(15),
  \`regexten\` varchar(20),
  \`rtpholdtimeout\` varchar(15),
  \`rtpkeepalive\` varchar(15),
  \`rtptimeout\` varchar(15),
  \`subscribemwi\` varchar(10),
  \`usereqphone\` varchar(10),
  \`vmexten\` varchar(20),
  \`disallow\` varchar(20),
  \`useragent\` varchar(20)
) */;"

$MY_MYSQL -D asterisk -e "$MY_VIEW"
$MY_MYSQL -e "DROP DATABASE \`gemeinschaft_schema\`; DROP DATABASE \`asterisk_new\`;"
#select new.TABLE_NAME, new.COLUMN_NAME, new.COLUMN_TYPE from new, old where new.TABLE_NAME=old.TABLE_NAME and new.COLUMN_NAME=old.COLUMN_NAME and new.COLUMN_TYPE != old.COLUMN_TYPE

# |  ast_queue_members |         static |    0 | YES | tinyint |        tinyint(1) |                |     |
# |  ast_queue_members |       uniqueid | NULL |  NO |     int | int(100) unsigned | auto_increment | PRI |
# |  ast_queue_members |         paused | NULL | YES |     int |            int(1) |                |     |
# |         ast_queues |     _sysrec_id |    0 |  NO |     int |  int(10) unsigned |                |     |
# |         ast_queues |    _min_agents |    0 | YES |     int |  int(10) unsigned |                |     |
# |     ast_sipfriends |    accountcode | NULL | YES | varchar |       varchar(20) |                |     |


