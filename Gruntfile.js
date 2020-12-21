module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            rename: {
                command:
                    'cp clearpay.zip clearpay-$(git rev-parse --abbrev-ref HEAD).zip \n'
            },
            autoindex: {
                command:
                    'composer global require pagantis/autoindex \n' +
                    'php ~/.composer/vendor/pagantis/autoindex/index.php ./ || true \n' +
                    'php /home/circleci/.config/composer/vendor/pagantis/autoindex/index.php . || true \n'

            },
            composerProd: {
                command: 'composer install --no-dev'
            },
            composerDev: {
                command: 'composer install --ignore-platform-reqs'
            },
            runTestPrestashop17: {
                command:
                    'docker-compose down\n' +
                    'docker-compose up -d selenium\n' +
                    'docker-compose up -d prestashop17-test\n' +
                    'echo "Creating the prestashop17-test"\n' +
                    'sleep 100\n' +
                    'date\n' +
                    'docker-compose logs prestashop17-test\n' +
                    'set -e\n' +
                    'vendor/bin/phpunit --group prestashop17basic\n' +
                    'vendor/bin/phpunit --group prestashop17install\n' +
                    'vendor/bin/phpunit --group prestashop17register\n' +
                    'vendor/bin/phpunit --group prestashop17buy\n' +
                    'vendor/bin/phpunit --group prestashop17advanced\n' +
                    'vendor/bin/phpunit --group prestashop17validate\n' +
                    'vendor/bin/phpunit --group prestashop17controller\n'
            },
            runTestPrestashop16: {
                command:
                    'docker-compose down\n' +
                    'docker-compose up -d selenium\n' +
                    'docker-compose up -d prestashop16-test\n' +
                    'echo "Creating the prestashop16-test"\n' +
                    'sleep  90\n' +
                    'date\n' +
                    'docker-compose logs prestashop16-test\n' +
                    'set -e\n' +
                    'vendor/bin/phpunit --group prestashop16basic\n' +
                    'vendor/bin/phpunit --group prestashop16install\n' +
                    'vendor/bin/phpunit --group prestashop16register\n' +
                    'vendor/bin/phpunit --group prestashop16buy\n' +
                    'vendor/bin/phpunit --group prestashop16advanced\n' +
                    'vendor/bin/phpunit --group prestashop16validate\n' +
                    'vendor/bin/phpunit --group prestashop16controller\n'
            },
        },
        compress: {
            main: {
                options: {
                    archive: 'clearpay.zip'
                },
                files: [
                    {src: ['controllers/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['classes/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['docs/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['override/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['logs/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['vendor/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['translations/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['upgrade/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['optionaloverride/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['oldoverride/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['sql/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['lib/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['defaultoverride/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: ['views/**'], dest: 'clearpay/', filter: 'isFile'},
                    {src: '.htaccess', dest: 'clearpay/'},
                    {src: 'index.php', dest: 'clearpay/'},
                    {src: 'clearpay.php', dest: 'clearpay/'},
                    {src: 'logo.png', dest: 'clearpay/'},
                    {src: 'README.md', dest: 'clearpay/'}
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.registerTask('default', [
        'shell:composerProd',
        'shell:autoindex',
        'compress',
        'shell:rename',
        'shell:composerDev'
    ]);

    //manually run the selenium test: "grunt shell:testPrestashop16"
};