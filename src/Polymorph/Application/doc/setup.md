## Setup

### Front Controller

A working front controller script is provided at `src/index.php`.
See the "Web Server Setup" section below for a few sample setups.

### Configuration

The default front controller expects a configuration file at 
`/config/app-config.json` which extends the base configuration from
`src/Polymorph/Application/config/base-config.json`.

### Web Server Setup

* Sample Apache `.htaccess` configuration:

		RewriteEngine On
		RewriteBase /

		# Hide the project's data directory
		RedirectMatch 404 /data/

		# Hide configuration files
		RedirectMatch 404 /config/

		# Redirect dynamic requests to the front controller.
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteCond %{REQUEST_FILENAME} !/(my-browsable-dir)/.*
		RewriteRule .* vendor/bnowack/polymorph/src/index.php [L]

		# Compress text output
		<IfModule mod_deflate.c>
			AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json
			<IfModule mod_headers.c>
				Header append Vary User-Agent
			</IfModule>
			BrowserMatch ^Mozilla/4 gzip-only-text/html
			BrowserMatch ^Mozilla/4\.0[678] no-gzip
			BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
		</IfModule>


* Sample nginx configuration:

		server {
			listen 80;
			server_name _;        
			root /srv/www;

			location / {
				index   index.html index.htm index.php;
				autoindex on;
			}

			location ~ \.php$ {
				fastcgi_pass unix:/var/run/php5-fpm.sock;
				fastcgi_index index.php;
				include fastcgi_params;
				fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			}

			# block data and config directories
			location ~* ^/(?<project>[a-z0-9_\-\.]+)/(data|config) {
				return 404;
			}

			# projects
			location ~* ^/(?<project>[a-z0-9_\-\.]+) {
				autoindex on;
				index   index.html index.htm index.php;
				try_files $uri $uri/ /PROJECT/vendor/bnowack/polymorph/src/index.php$is_args$args;
			} 
		}

