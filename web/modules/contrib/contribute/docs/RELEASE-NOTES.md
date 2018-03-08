
Steps for creating a new release
--------------------------------

  1. Review code
  2. Run tests
  3. Generate release notes
  4. Tag and create a new release

1. Review code
--------------

[Online](http://pareview.sh)

    http://git.drupal.org/project/contribute.git 8.x-1.x

[Commandline](https://www.drupal.org/node/1587138)

    # Check Drupal coding standards
    phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme,css,info modules/sandbox/contribute
    
    # Check Drupal best practices
    phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme,js,css,info modules/sandbox/contribute


2. Run tests
------------

    # Execute all Webform PHPUnit tests.
    cd core
    php ../vendor/phpunit/phpunit/phpunit --printer="\Drupal\Tests\Listeners\HtmlOutputPrinter" --group contribute


3. Generate release notes
-------------------------

[Git Release Notes for Drush](https://www.drupal.org/project/grn)

    drush release-notes --nouser 8.x-1.0-VERSION 8.x-1.x


4. Tag and create a new release
-------------------------------

[Tag a release](https://www.drupal.org/node/412780)

    git tag 8.x-5.0-VERSION
    git push --tags
    git push origin tag 8.x-5.0-VERSION

[Create new release](https://www.drupal.org/node/add/project-release/2640714)
