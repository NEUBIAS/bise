# Bioimage Infromation Search Engine (BISE)

[![Image.sc forum](https://img.shields.io/badge/dynamic/json.svg?label=forum&url=https%3A%2F%2Fforum.image.sc%2Ftags%2Fbiii.json&query=%24.topic_list.tags.0.topic_count&colorB=brightgreen&suffix=%20topics&logo=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAABPklEQVR42m3SyyqFURTA8Y2BER0TDyExZ+aSPIKUlPIITFzKeQWXwhBlQrmFgUzMMFLKZeguBu5y+//17dP3nc5vuPdee6299gohUYYaDGOyyACq4JmQVoFujOMR77hNfOAGM+hBOQqB9TjHD36xhAa04RCuuXeKOvwHVWIKL9jCK2bRiV284QgL8MwEjAneeo9VNOEaBhzALGtoRy02cIcWhE34jj5YxgW+E5Z4iTPkMYpPLCNY3hdOYEfNbKYdmNngZ1jyEzw7h7AIb3fRTQ95OAZ6yQpGYHMMtOTgouktYwxuXsHgWLLl+4x++Kx1FJrjLTagA77bTPvYgw1rRqY56e+w7GNYsqX6JfPwi7aR+Y5SA+BXtKIRfkfJAYgj14tpOF6+I46c4/cAM3UhM3JxyKsxiOIhH0IO6SH/A1Kb1WBeUjbkAAAAAElFTkSuQmCC)](https://forum.image.sc/tag/biii)

branch: neubias/bise master  
[![Build Status](https://travis-ci.org/NeuBIAS/bise.svg?branch=master)](https://travis-ci.org/NeuBIAS/bise)

<https://biii.eu>

branch: neubias/bise dev  
[![Build Status](https://travis-ci.org/NeuBIAS/bise.svg?branch=dev)](https://travis-ci.org/NeuBIAS/bise)

<https://test.biii.eu>

DEVELOPMENT PROCESS:

For administrator: no change in drupal should be done directly on test.biii.eu (dev) or biii.eu (prod). 
Instead, please follow the process detailed in our wiki, as set up by Kota Miura: 

https://github.com/NEUBIAS/bise/wiki/BISE-development

A summary is provided here for your convenience: 
```
  with local test.biii(dev)
        import production database. (lando db-import)
        import configration from file to database. 
        drush cr, 
        change the setting in GUI as requested
        export configuration
        commit changes to dev, push to upstream dev
    pull from inside SERVER test, import configuration. drush cr, check test.biii.eu

    locally, switch to the production branch, cherrypick only relevant commits from dev
        drush config-import & cr to see if it is OK.  
        if working, push to upstream production. 
    pull from inside the SERVER production, import configuration changes, drush cr
```
---
A bit of development history: the project biii started with a classic "Drupal-composer/drupal-project" ([see here](https://github.com/drupal-composer/drupal-project)) in 2017, and then in 2021, the framework switched to the "drupal/core-recommended"([see here](https://github.com/drupal/core-recommended)) project.  
