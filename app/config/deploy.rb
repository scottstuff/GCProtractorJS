namespace :ov do
  namespace :doctrine do
    desc "Loads data fixtures with --append flag"
    task :load_fixtures, :roles => :app, :except => { :no_release => true } do
      run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} doctrine:fixtures:load --append #{doctrine_em_flag}'", :once => true
    end
  end

  # a version of assetic:dump with no flags, for development
  namespace :assetic do
    desc "Dumps all assets to the filesystem"
    task :dump, :roles => :app,  :except => { :no_release => true } do
    capifony_pretty_print "--> Dumping all assets to the filesystem"
    run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} assetic:dump'"
    capifony_puts_ok
    end
  end
end

namespace :gotchosen do
  desc "Rebuilds translations"
  task :rebuild_translations, :roles => :app, :except => { :no_release => true } do
    run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} gotchosen:translator:cache --env=#{symfony_env_prod}'", :once => true
  end
end

set :stages,        %w(dev staging prod)
set :default_stage, "dev"
set :stage_dir,     "app/config"
require 'capistrano/ext/multistage'

set :application, "GotChosen"
set :user,        "gotchosen"
set :app_path,    "app"

set :repository,  "git@github.com:gotchosen/GotChosenPHP.git"
set :scm,         :git

set :model_manager, "doctrine"

set :keep_releases,  3

set :use_sudo, false
set :shared_files, ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", app_path + "/var", web_path + "/uploads", "vendor"]
set :use_composer, true
set :branch, "master"
set :group_writable, false
default_run_options[:pty] = true
before "symfony:cache:warmup", "symfony:doctrine:migrations:migrate"
before "symfony:cache:warmup", "ov:doctrine:load_fixtures"
after "symfony:cache:warmup", "gotchosen:rebuild_translations"

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL
