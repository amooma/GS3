#!/bin/bash -x
########################
#
# Author: Sascha Daniels <sd@alternative-solution.de>
# 
# http://www.alternative-solution.de
#
# This software ist NOT developed by amooma GmbH and will NOT
# be supported by amooma GmbH
#
# If you have problems with this software feel free to contact the author.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
# MA 02110-1301, USA.
#########################
which mysql > /dev/null|| apt-get install -y mysql-client-5.1 

function MY_ERROR(){
echo -e "$1";
exit 1;

}

echo -e "	\n\n \
	This script will update the MySQL tables of your Gemeinschaft 2.3.1 \n \
	installation to the new table structure of Gemeinschaft 3.0. \n \
	Please first read the following lines carefull before you start over: \n\
	\n\n \
	This script was written for my internal use to make updates easier.\n \
	amooma GmbH wil NOT support this script!\n \
	If you have trouble with this script, feel free to send me a message:\n \
	\n\n \
	Sascha Daniels <sd@alternative-solution.de> \n\n\n \
	1. Make a backup of your database!\n \
	2. Old database must be named 'asterisk' \n \
	3. Database 'asterisk_new' must NOT exist. \n \
	4. Database 'gemeinschaft_schema' must NOT exist. \n \
	5. Be shure /opt/gemeinschaft-source/usr/share/doc/gemeinschaft/asterisk.sql is present.\n \
	\n\n \
	You will prompted for your MySQL root password. \n
	The password will only be stored in a variable during runtime. \n \
	\n\n \
	There is NO warranty or guaranty!\n \
	Use at your own risk!\n \
	If you want to know what is really going on, open this script with your editor!\n \
	\n\n \
	Backup your data! \n \
	\n\n \
	Do you want to continue (y|n)? \n \
		"  
read answer;
case $answer in n|N)
		echo "See you next time.";
		exit 0;
	;;
	y|Y)
	;;
	*)
		echo "Don't know what you mean with: $answer";
		exit 1;
	;;
esac
echo "What is your MYSQL root password?"
read n
MY_MYSQL_PASS="$n";


MY_MYSQL="mysql -uroot -N -p$MY_MYSQL_PASS " 
$MY_MYSQL -e "SHOW DATABASES" > /dev/null ||  MY_ERROR "Can't connect to database.";

$MY_MYSQL -e "CREATE DATABASE gemeinschaft_schema;" > /dev/null || MY_ERROR "Can't create database gemeinschaft_schema.";

$MY_MYSQL -D gemeinschaft_schema -e "CREATE VIEW old AS SELECT * FROM information_schema.columns WHERE table_schema='asterisk';" 
$MY_MYSQL -D gemeinschaft_schema -e "CREATE VIEW new AS SELECT * FROM information_schema.columns WHERE table_schema='asterisk_new';"

$MY_MYSQL -e "CREATE DATABASE asterisk_new" > /dev/null || MY_ERROR "Can't create database asterisk_new";

test -f /opt/gemeinschaft-source/usr/share/doc/gemeinschaft/asterisk.sql || MY_ERROR "/usr/src/gemeinschaft/usr/share/doc/gemeinschaft/asterisk.sql not found";

sed -e 's/asterisk/asterisk_new/g' /opt/gemeinschaft-source/usr/share/doc/gemeinschaft/asterisk.sql  | $MY_MYSQL -D asterisk_new

MY_FIELDS="select new.TABLE_NAME, new.COLUMN_NAME, new.COLUMN_DEFAULT, new.IS_NULLABLE, new.DATA_TYPE, new.COLUMN_TYPE, new.EXTRA, new.COLUMN_KEY from new, old where new.column_name=old.column_name and new.table_name=old.table_name and new.TABLE_NAME !='ast_sipfriends_gs' and (new.DATA_TYPE != old.DATA_TYPE or new.COLUMN_TYPE != old.COLUMN_TYPE);"

echo -e "############\n I will change these columns:\n"

$MY_MYSQL --batch -D gemeinschaft_schema  -e "$MY_FIELDS" | tr '\t' '|' | while IFS='|' read TABLE COLUMN DEFAULT NULLABLE DATATYPE COLUMNTYPE EXTRA KEY  
do 
        echo -e "$TABLE.$COLUMN\n";
		if [ "$DEFAULT" != "NULL" ]
        then
			DEFAULT="default '$DEFAULT'";
		fi
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
	    MY_CHANGE="alter table \`$TABLE\` change \`$COLUMN\` \`$COLUMN\` $COLUMNTYPE $EXTRA $NULLABLE $DEFAULT;"
        $MY_MYSQL -D asterisk -e "$MY_CHANGE";
done;



MY_CHANGES="select new.TABLE_NAME, new.COLUMN_NAME, new.COLUMN_DEFAULT, new.IS_NULLABLE, new.DATA_TYPE, new.COLUMN_TYPE, new.EXTRA, new.COLUMN_KEY from new left join old on new.column_name=old.column_name and new.table_name=old.table_name where old.column_name is null and new.table_name in (select distinct(table_name) from old) and new.TABLE_NAME !='ast_sipfriends_gs';"

echo -e "############\n I will add these columns:\n"

$MY_MYSQL --batch -D gemeinschaft_schema  -e "$MY_CHANGES" | tr '\t' '|' | while IFS='|' read TABLE COLUMN DEFAULT NULLABLE DATATYPE COLUMNTYPE EXTRA KEY  
do 
	echo -e "$TABLE.$COLUMN\n";
	DEFAULT="default $DEFAULT";

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

MY_MISSING="select distinct(new.table_name) from new left join old on old.table_name=new.table_name where old.table_name is null and new.table_name !='ast_sipfriends_gs';"

echo -e "############\n I will create these tables:\n"
for i in $($MY_MYSQL -D gemeinschaft_schema  -e "$MY_MISSING");
	do
		echo -e "$i \n";
		$MY_MYSQL -D asterisk -e "create table $i like asterisk_new.$i;";
		$MY_MYSQL -D asterisk -e "INSERT INTO $i (SELECT * FROM asterisk_new.$i);";
	done

	echo -e "############\n Creating view ast_sipfriends_gs\n\n";
mysqldump -uroot -p$MY_MYSQL_PASS asterisk_new ast_sipfriends_gs | $MY_MYSQL -D asterisk
$MY_MYSQL -e "DROP DATABASE \`gemeinschaft_schema\`; DROP DATABASE \`asterisk_new\`;"

echo -e "############\n	It looks like we are done.\n \
	You will need a user with admin rights. \n \
	Please use /opt/gemeinschaft/scripts/gs-group-member-add \n \
	Group is 'admins'.\n
	"
