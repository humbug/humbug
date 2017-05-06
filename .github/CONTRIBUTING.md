# How to contribute

Contributions are always welcome. Here are a few guidelines to be aware of:
 
 - Include unit tests and/or behat features (where relevant) for new behaviours introduced by PRs.
 - Include README changes where relevant to do so.
 - We use [SemVer v2.0.0](http://semver.org/), so please check that PRs do not break public APIs unless intended for a future major version. The current major version is Humbug 1.0, however all future development is being directed towards Humbug 2.0. Backwards compatibility is therefore not a significant concern until 2.0 is released.
 - All code must follow the `PSR-2` coding standard. Please see [PSR-2](http://www.php-fig.org/psr/psr-2/) for more details. To make this as easy as possible, we use PHP_CodeSniffer which is accessible after `composer install` using two simple composer scripts: `composer cs-check` and `composer cs-fix`. The coding standard is enforced in our PR checks using [StyleCI](https://styleci.io).