# php_benchmark_runner
This is a personal project and it represents a Symfony bundle implemented based on the PHPBench project (a benchmark runner for PHP).

## Usage
### Installation
Using the latest versioning tag, update the composer.json file:
```
"repositories": [
        {
            "type": "package",
            "package": {
                "name": "mep_project/php_benchmark_runner",
                "version": "0.0.6",
                "source": {
                    "url": "https://github.com/CatalinaAnghel/php_benchmark_runner.git",
                    "type": "git",
                    "reference": "18c305d8d30701b85b0c160ed4bba0f8f45c65ba"
                },
                "autoload": {
                    "psr-4": {
                        "MepProject\\PhpBenchmarkRunner\\": "src/",
                        "MepProject\\PhpBenchmarkRunner\\Service\\": "src/Service/",
                        "MepProject\\PhpBenchmarkRunner\\Helper\\": "src/Helpers/"
                    }
                }
            }
        }
    ]
```
### Configuration
Create a yaml file in the config/packages directory. E.g.:
```
// config/packages/php_benchmark_runner.yaml
php_benchmark_runner:
  locator: 'app.modules_locator'
  providers_locator: 'app.providers_locator'
  hooks_locator: 'app.hooks_locator'
  limit:
      revolutions: 15000
      iterations: 10000
```

Register the package as a bundle:
```
// config/bundles.php
return [
    ...
    MepProject\PhpBenchmarkRunner\PhpBenchmarkRunnerBundle::class => ['all'=>true]
];
```

## Credits
This project has been implemented based on the PHPBench project.

For more details about this benchmark runner, please check the documentation for the **PHPBench** project which can be found using the following URL: https://phpbench.readthedocs.io/en/latest 

## License
[MIT](https://choosealicense.com/licenses/mit/)