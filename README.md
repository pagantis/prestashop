# Módulo de prestashop para Paga Más Tarde


## Instrucciones de Instalación

1. Crea tu cuenta en pagamastarde.com si aún no la tienes [desde aquí](https://bo.pagamastarde.com/users/sign_up)
2. Descarga el módulo de [aquí](https://github.com/pagantis/pagamastarde-prestashop/releases/latest)
3. Instala el módulo en tu prestashop
4. Configuralo con la información de tu cuenta que encontrarás en [el panel de gestión de Paga+Tarde](https://bo.pagamastarde.com/shop). Ten en cuenta que para hacer cobros reales deberás activar tu cuenta de Paga+Tarde.

## Modo real y modo de pruebas

Tanto el módulo como Paga+Tarde tienen funcionamiento en real y en modo de pruebas independientes. Debes introducir las credenciales correspondientes del entorno que desees usar.

### Soporte

El manual de instalación y uso del módulo se puede consultar [aquí](https://github.com/pagantis/pagamastarde-prestashop/blob/master/Guide.pdf).

Si tienes alguna duda o pregunta no tienes más que escribirnos un email a [soporte@pagamastarde.com]

## Development Instructions:

To develop or improve this module you need to have installed in your environment
    * NPM
    * Composer
    
To make the module operative you need to download the dependencies, 

    npm install
    composer install
    
Once both dependencies are ready you can generate the specific module files using

    grunt default
    
Grunt will compress the CSS and the JS and generate a zip file with the necessary files to push
to the market.

You can always do a symbolic link from your local installation of prestashop in order to verify
the functionality of the code.


### Testing and Improvements

* Doing some phpUnit testing on the module.
* Improving the code structure to make it more human.
