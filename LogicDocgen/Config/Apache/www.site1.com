<VirtualHost *:80>
	ServerAdmin	webmaster@site1.com
	ServerName	www.site1.com

	DocumentRoot	/var/www/www.site1.com

	<Directory	/var/www/www.site1.com>
		AuthType		Basic
		AuthBasicProvider	ldap
		AuthzLDAPAuthoritative	on
		AuthName 		"freebird.com Petit Bapis"
		AuthLDAPURL 		ldap://127.0.0.1:389/ou=people,dc=freebird,dc=com

		require			valid-user
	</Directory>

	LogLevel	warn

	ErrorLog	/var/log/apache2/www.site1.com/error.log
	CustomLog	/var/log/apache2/www.site1.com/access.log	combined
</VirtualHost>
