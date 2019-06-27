<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'photo-share');

// Project repository
set('repository', 'git@github.com:adrientiburce/photoShare.git');
set('branch', 'prod');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', ['.env.local']);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', ['public/uploads', 'var']);
set('allow_anonymous_stats', false);

// Hosts
host('webrush')
    ->stage('prod')
    ->set('deploy_path', '/var/www/{{application}}')
    ->set('env', [
        'APP_ENV'=>'prod',
    ]);

set('bin/console', function () {
    return parse('{{bin/php}} {{release_path}}/bin/console --no-interaction');
});

task('database:migrate', function () {
    $options = '--allow-no-migration';
    if (get('migrations_config') !== '') {
        $options = sprintf('%s --configuration={{release_path}}/{{migrations_config}}', $options);
    }
    $options = "";
    run(sprintf('{{bin/console}} doctrine:migrations:migrate %s', $options));
});

task('fixtures:load', function () {
    run('{{bin/console}} doctrine:fixtures:load --env=dev');
});

task('deploy:vendors', function () {
    // Your custom update code
    run("export APP_ENV='prod'; cd {{release_path}} && composer install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader");
});

task('deploy:cache:clear', function() {
    run("cd {{release_path}} && rm -fr var/cache/prod && rm -fr var/cache/dev");
});

// task('deploy:cache:warmup', function() {
//     run("export APP_ENV='prod'; cd {{release_path}} && APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup");
// });

task('deploy:change:acl', function() {
    run("cd {{release_path}} && setfacl -R 'u:www-data:rwx' var/ && setfacl -Rm 'u:www-data:rwx' var/");
});

task('update:env', function () {
    run("rm .env; rm .env.local");
    run("ln -s /var/www/{{application}}/shared/.env.dist .env.local");
    run("rm .env");
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// we enable env variables for prod
//after('deploy:update_code', 'update:env');

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');
after('database:migrate', 'fixtures:load');

/**
 * Main task
 */
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:clear_paths',
    'deploy:create_cache_dir',
    'deploy:shared',
    'deploy:assets',
    'deploy:vendors',
    //'deploy:build-prod', // build with encore
    'deploy:assetic:dump',
    'deploy:cache:clear',
    // 'deploy:cache:warmup', //issue with own after cache:warmup www-data :no acces
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);
