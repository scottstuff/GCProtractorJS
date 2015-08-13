set :domain,      "gotchosen.com"
set :deploy_to,   "/var/www/vhosts/gotchosen"

set :symfony_env_prod, "prod"

set :branch, "master"

role :web,        'web001.prod', 'web002.prod'        # Your HTTP server, Apache/etc
role :app,        'web001.prod', :primary => true
role :app,        'web002.prod'
#role :db,         'web001.prod', :primary => true       # This is where Symfony2 migrations will run

set :dump_assetic_assets, true
