set :domain,      "gotscholarship.com"
set :deploy_to,   "/var/www/vhosts/gotchosen-dev"

set :symfony_env_prod, "dev"

set :branch, fetch(:branch, "master")

role :web,        'web001.dev', 'web002.dev'        # Your HTTP server, Apache/etc
role :app,        'web001.dev', :primary => true
role :app,        'web002.dev'
#role :db,         'web001.dev', :primary => true  # This is where Symfony2 migrations will run LOL NOPE, runs on :app

after "symfony:cache:warmup", "ov:assetic:dump"
