##
## Kristoffer Langeland Knudsen
## rainbowponyprincess@gmail.com
##

composer:
	php -r "readfile('https://getcomposer.org/installer');" | php
	php composer.phar install

mysql:
	php share/setup/mysql.init_user.php
	php share/setup/mysql.init_tables.php

# EOF

