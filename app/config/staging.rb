set :domain,      "gotscholarship.com"
set :deploy_to,   "/var/www/vhosts/gotchosen-dev"

set :symfony_env_prod, "dev"

set :branch, "master"

role :web,        'web001.test', 'web002.test'        # Your HTTP server, Apache/etc
role :app,        'web001.test', :primary => true
role :app,        'web002.test'
#role :db,         'web001.test', :primary => true  # This is where Symfony2 migrations will run LOL NOPE, runs on :app

after "symfony:cache:warmup", "ov:assetic:dump"

