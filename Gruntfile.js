module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            rename: {
                command:
                    'cp pagantis.zip pagantis-$(git rev-parse --abbrev-ref HEAD).zip \n'
            },
            autoindex: {
                command:
                    'composer global require pagantis/autoindex \n' +
                    'php ~/.composer/vendor/pagantis/autoindex/index.php || true \n' +
                    'php /home/circleci/.config/composer/vendor/pagantis/autoindex/index.php . \n'

            },
            composerProd: {
                command: 'composer install --no-dev'
            },
            composerDev: {
                command: 'composer install'
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
            runTestPrestashop15: {
                command:
                    'docker-compose down\n' +
                    'docker-compose up -d selenium\n' +
                    'docker-compose up -d prestashop15-test\n' +
                    'echo "Creating the prestashop15-test"\n' +
                    'sleep 90\n' +
                    'date\n' +
                    'docker-compose logs prestashop15-test\n' +
                    'set -e\n' +
                    'vendor/bin/phpunit --group prestashop15basic\n' +
                    'vendor/bin/phpunit --group prestashop15install\n' +
                    'vendor/bin/phpunit --group prestashop15register\n' +
                    'vendor/bin/phpunit --group prestashop15buy\n' +
                    'vendor/bin/phpunit --group prestashop15validate\n' +
                    'vendor/bin/phpunit --group prestashop15controller\n'
            }
        },
        compress: {
            main: {
                options: {
                    archive: 'pagantis.zip'
                },
                files: [
                    {src: ['controllers/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['classes/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['docs/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['override/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['logs/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['vendor/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['translations/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['upgrade/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['optionaloverride/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['oldoverride/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['sql/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['lib/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['defaultoverride/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: ['views/**'], dest: 'pagantis/', filter: 'isFile'},
                    {src: 'index.php', dest: 'pagantis/'},
                    {src: 'pagantis.php', dest: 'pagantis/'},
                    {src: 'logo.png', dest: 'pagantis/'},
                    {src: 'logo.gif', dest: 'pagantis/'},
                    {src: 'LICENSE.md', dest: 'pagantis/'},
                    {src: 'CONTRIBUTORS.md', dest: 'pagantis/'},
                    {src: 'README.md', dest: 'pagantis/'}
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
        'shell:rename'
    ]);

    //manually run the selenium test: "grunt shell:testPrestashop16"
};
