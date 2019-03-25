module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            rename: {
                command:
                    'cp paylater.zip paylater-$(git rev-parse --abbrev-ref HEAD).zip \n'
            },
            autoindex: {
                command:
                    'php vendor/pagamastarde/autoindex/index.php . \n' +
                    'rm -rf vendor/pagamastarde/autoindex \n'
            },
            composerProd: {
                command: 'rm -rf vendor && composer install --no-dev'
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
                    archive: 'paylater.zip'
                },
                files: [
                    {src: ['controllers/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['classes/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['docs/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['override/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['logs/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['vendor/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['translations/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['upgrade/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['optionaloverride/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['oldoverride/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['sql/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['lib/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['defaultoverride/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: ['views/**'], dest: 'paylater/', filter: 'isFile'},
                    {src: 'index.php', dest: 'paylater/'},
                    {src: 'paylater.php', dest: 'paylater/'},
                    {src: 'logo.png', dest: 'paylater/'},
                    {src: 'logo.gif', dest: 'paylater/'},
                    {src: 'LICENSE.md', dest: 'paylater/'},
                    {src: 'CONTRIBUTORS.md', dest: 'paylater/'},
                    {src: 'README.md', dest: 'paylater/'}
                ]
            }
        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.registerTask('default', [
        'shell:composerDev',
        'shell:composerProd',
        'shell:autoindex',
        'compress',
        'shell:composerDev',
        'shell:rename'
    ]);

    //manually run the selenium test: "grunt shell:testPrestashop16"
};
