[![Build Status](https://travis-ci.org/francodacosta/caparica.png?branch=master)](https://travis-ci.org/francodacosta/caparica) [![Total Downloads](https://poser.pugx.org/francodacosta/caparica-bundle/downloads.svg)](https://packagist.org/packages/francodacosta/caparica-bundle) [![Latest Stable Version](https://poser.pugx.org/francodacosta/caparica-bundle/v/stable.svg)](https://packagist.org/packages/francodacosta/caparica-bundle)

# Getting started with Caparica Bundle

## Instalation


    1. Download CaparicaBundle using composer
    2. Enable the Bundle
    3. add a client
    4. Configure your controller

### Step1: Download CaparicaBundle using composer

Add CaparicaBundle by running the command:

``` bash
$ php composer.phar require francodacosta/caparica-bundle '~1.0'
```

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Francodacosta\CaparicaBundle\FrancodacostaCaparicaBundle(),
    );
}
```

### Step 3: Add a client

So that the server can verify the client signature it needs to know the client/api id and secret

If using the ```YamlClientProvider``` edit the file : ```app/config/caparica.yml```

```yaml

client_alias:
    code: "the client code"
    secret: "the client api secret"

```

### Step 3: Configure your controller

Make sure you controller implements the ```Francodacosta\CaparicaBundle\Controller\CaparicaControllerInterface```.
The  ```Francodacosta\CaparicaBundle\Controller\CaparicaController``` extends the Symfony controller and has some handy methods.

and that's it all controller actions accessed via a route will have to be signed
