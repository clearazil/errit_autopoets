<?php
namespace Deployer;

require 'recipe/symfony.php';

if (file_exists('deployer-config.php')) {
    require 'deployer-config.php';
}

// Project name
set('application', 'errit_autopoets');
set('bin/php', 'php');
set('bin/console', '{{release_path}}/bin/console');

// Project repository
set('repository', getenv('REPOSITORY_URL'));
set('branch' , 'development');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// dump assetic assets
set('dump_assets', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);


$userNameHost = getenv('SSH_USER') . '@' . getenv('SSH_HOST');

host($userNameHost)
    ->stage('staging')
    ->set('deploy_path', '/var/www/{{application}}');

// parameters for parameters.yml
$parameters = [
    'database_host' => getenv('MYSQL_HOST'),
    'database_port' => getenv('MYSQL_PORT'),
    'database_name' => getenv('MYSQL_DATABASE'),
    'database_user' => getenv('MYSQL_USER'),
    'database_password' => getenv('MYSQL_PASSWORD'),
    'mailer_transport' => getenv('MAILER_TRANSPORT'),
    'mailer_port' => getenv('MAILER_PORT'),
    'mailer_encryption'  => getenv('MAILER_ENCRYPTION'),
    'mailer_host' => getenv('MAILER_HOST'),
    'mailer_user' => getenv('MAILER_USER'),
    'mailer_password' => getenv('MAILER_PASSWORD'),
    'mailer_auth_mode'  => getenv('MAILER_AUTH_MODE'),
    'secret' => getenv('SYMFONY_SECRET'),
];
// Tasks

// create the parameters.yml file
task('create_parameters', function () use ($parameters) {
    run('cd {{release_path}}');
    run('rm -rf {{release_path}}/app/config/parameters.yml');
    run('touch {{release_path}}/app/config/parameters.yml');
    run('echo \'parameters:\' >> {{release_path}}/app/config/parameters.yml');
    foreach ($parameters as $key => $parameter) {
        run('echo \'    ' . $key . ': ' . $parameter . '\' >> {{release_path}}/app/config/parameters.yml');

    }
});

before('deploy:vendors', 'create_parameters');

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');



// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

