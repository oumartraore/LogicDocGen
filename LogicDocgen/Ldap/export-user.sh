#! /bin/bash

PLINES=`cat /etc/passwd`
IFS=$(echo -en "\n\b")

for line in $PLINES; do
	USERLOGIN=`echo $line |cut -d ":" -f1`
	sline=`grep "^$USERLOGIN:" /etc/shadow`
	LOGINSHELL=`echo $line |cut -d ":" -f7`
	UIDNUMBER=`echo $line |cut -d ":" -f3`
	GIDNUMBER=`echo $line |cut -d ":" -f4`
	HOMEDIRECTORY=`echo $line |cut -d ":" -f6`
	GECOS=`echo $line |cut -d ":" -f5`
	PASSWORD=`echo $sline |cut -d ":" -f2`
	LASTCHANGE=`echo $sline |cut -d ":" -f3`
	MAX=`echo $sline |cut -d ":" -f5`
	WARNING=`echo $sline |cut -d ":" -f6`
	
	if [[ "$UIDNUMBER" -ge 500 && "$UIDNUMBER" -le 6000 || "$UIDNUMBER" -eq 0 ]]; then
		echo "dn: uid=$USERLOGIN,ou=people,dc=freebird,dc=com"
		echo "objectClass: account"
		echo "objectClass: posixAccount"
		echo "objectClass: shadowAccount"
		echo "uid: $USERLOGIN"
		echo "cn: $USERLOGIN"
		echo "userPassword: {crypt}$PASSWORD"
		echo "shadowLastChange: $LASTCHANGE"
		echo "shadowMax: $MAX"
		echo "shadowWarning: $WARNING"
		echo "loginShell: $LOGINSHELL"
		echo "uidNumber: $UIDNUMBER"
		echo "gidNumber: $GIDNUMBER"
		echo "homeDirectory: $HOMEDIRECTORY"
		echo "gecos: $GECOS"
		echo ""
	fi
done
