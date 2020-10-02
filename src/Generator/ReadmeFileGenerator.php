<?php

namespace ComposerFallback\PackageGenerator\Generator;

use ComposerFallback\PackageGenerator\Definition\AlternativeDefinition;
use ComposerFallback\PackageGenerator\Definition\FallbackDefinition;
use ComposerFallback\PackageGenerator\Repository\RepositoryWriter;

class ReadmeFileGenerator
{
    private $writer;

    public function __construct(RepositoryWriter $writer)
    {
        $this->writer = $writer;
    }

    public function generate(AlternativeDefinition $alternative): void
    {
        $content = <<<README
# Fallback for `{{FALLBACK_NAME}}` using `{{ALTERNATIVE_NAME}}`

Provides a metapackage for library needing `{{FALLBACK_NAME}}` 
that fallback to a default implementation when user does not meet
the initial requirements.

## Usage

```shell
composer require "{{PACKAGE_FULLNAME}}:*"
```

Composer will, by preference:
- {{PREFERRED}}
- {{FALLBACKS}}

## How does it works

This package contains 2 versions:

1. The highest [1.1](https://github.com/{{PACKAGE_FULLNAME}}/blob/1.1/composer.json) needs {{PREFERRED_REQUIREMENTS}}.

1. The lowest [1.0](https://github.com/{{PACKAGE_FULLNAME}}/blob/1.0/composer.json) triggers install of {{ALTERNATIVE_REQUIREMENTS}}.

Composer will choose this highest version when {{PREFERRED_WHEN}}.
Otherwise, composer will choose the lowest version and in that case will 
download the following packages: {{ALTERNATIVE_REQUIREMENTS}}.

## What problem does it solve?

You are maintaining a library that need an implementation of `{{FALLBACK_NAME}}`,
but you don't want requiring a specific implementation. 

{{EXAMPLE_LIB}}

When end users requires you library with the following code 
```json
{
    "name": "end-user/app",
    "require": {
      "acme/lib": "^1.0"
    }
}
```

They probably ends with such error

```shell
composer up

Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - Installation request for acme/lib ^1.0 -> satisfiable by acme/lib[1.0].
    - acme/lib 1.0 requires {{PREFERRED_CONSTRAINT}} -> no matching package found.
```

You can ask user to install a random package, it works, but the DX is very bad,
and confusing at first.

By using `{{PACKAGE_FULLNAME}}`, 
{{SOLUTION}} 
or fallback to your default choice

### Example of user that meet the preferred requirements

```json
{
    "name": "end-user/app",
    "require": {
        "acme/lib": "^1.0",
        "third-party/provide-implementation": "^1.0"
    }
}
```
```shell
composer up
...
Package operations: 2 installs, 0 updates, 0 removals
  - Installing acme/lib (1.0)
  - Installing {{PACKAGE_FULLNAME}} (1.1)
```

### Example of user that fallback to your recommendations

```json
{
    "name": "end-user/app",
    "require": {
        "acme/lib": "^1.0"
    }
}
```
```shell
composer up
...
Package operations: 3 installs, 0 updates, 0 removals
  - Installing acme/lib (1.0)
  - Installing {{PACKAGE_FULLNAME}} (1.0)
  - Installing {{ALTERNATIVE_PACKAGE}} (1.0)
```

{{ALTERNATIVES}}## Contributing

This repository is automatically generated. If you want contributing and 
submitting an issue or a Pull Request, please use 
[composer-fallback/generator](https://github.com/composer-fallback/generator).
README;

        $this->writer->writeFile(
            $alternative,
            'README.md',
            trim(
                \strtr(
                    $content,
                    [
                        '{{PACKAGE_FULLNAME}}' => $alternative->getPackageFullName(),
                        '{{PREFERRED}}' => $this->getPreferredChoice($alternative),
                        '{{FALLBACKS}}' => 'otherwise, fallbacks to `'.\implode(
                                '` + `',
                                \array_keys($alternative->getPackage()->getRequirements())
                            ).'`',
                        '{{FALLBACK_NAME}}' => $alternative->getFallback()->getName(),
                        '{{ALTERNATIVE_NAME}}' => $alternative->getName(),
                        '{{EXAMPLE_LIB}}' => strtr(
                            $this->getExampleLib($alternative),
                            ['{{FALLBACK_NAME}}' => $alternative->getFallback()->getName()]
                        ),
                        '{{PREFERRED_CONSTRAINT}}' => $this->getPreferredConstraint($alternative),
                        '{{PREFERRED_REQUIREMENTS}}' => $this->getRequirements(
                            $alternative->getFallback()->getPreferredPackage()->getRequirements()
                        ),
                        '{{PREFERRED_WHEN}}' => strtr(
                            $this->getPreferredWhen($alternative),
                            ['{{FALLBACK_NAME}}' => $alternative->getFallback()->getName()]
                        ),
                        '{{ALTERNATIVE_REQUIREMENTS}}' => $this->getRequirements(
                            $alternative->getPackage()->getRequirements()
                        ),
                        '{{SOLUTION}}' => $this->getSolution($alternative),
                        '{{ALTERNATIVES}}' => $this->getAlternatives($alternative),
                        '{{ALTERNATIVE_PACKAGE}}' => $this->getAlternativePackage($alternative),
                    ]
                )
            )."\n"
        );
    }

    private function getPreferredChoice(AlternativeDefinition $alternative): string
    {
        $reqs = $alternative->getFallback()->getPreferredPackage()->getRequirements();
        if (count($reqs) === 0) {
            return 'use an implementation provided by the user';
        }
        if (count($reqs) > 1) {
            throw new \LogicException('Not implemented');
        }

        return 'check if user has `'.\array_keys($reqs)[0].(\array_values($reqs)[0] === '*' ? '' : \array_values(
                $reqs
            )[0]).'`';
    }

    private function getExampleLib(AlternativeDefinition $alternative): string
    {
        switch ($alternative->getFallback()->getType()) {
            case FallbackDefinition::TYPE_VIRTUAL:
                $code = <<<CODE
ie. You are maintainer of library that use the following composer.json
```json
{
  "name": "acme/lib",
  "require": {
    "{{FALLBACK_NAME}}": "^1.0",
  }
}
```
CODE;
                break;
            case FallbackDefinition::TYPE_POLYFILL:
                $code = '
ie. you need function defined in `{{FALLBACK_NAME}}`, but polyfill exists and would be enough.
';
                break;
            default:
                throw new \LogicException('Not implemented');
        }

        return trim($code);
    }

    private function getPreferredConstraint(AlternativeDefinition $alternative): string
    {
        switch ($alternative->getFallback()->getType()) {
            case FallbackDefinition::TYPE_VIRTUAL:
                return $alternative->getFallback()->getName().' ^1.0';
            case FallbackDefinition::TYPE_POLYFILL:
                $reqs = $alternative->getFallback()->getPreferredPackage()->getRequirements();
                if (count($reqs) !== 1) {
                    throw new \LogicException('Not implemented');
                }

                return \array_keys($reqs)[0].' '.\array_values($reqs)[0];
            default:
                throw new \LogicException('Not implemented');
        }
    }

    private function getRequirements(array $requirements): string
    {
        if (count($requirements) === 0) {
            return 'nothing more than your requirements';
        }

        $constraints = [];
        foreach ($requirements as $name => $constraint) {
            if ($constraint === '*') {
                $constraints[] = '`'.$name.'`';
            } else {
                $constraints[] = '`'.$name.': '.$constraint.'`';
            }
        }

        return implode(' + ', $constraints);
    }

    private function getPreferredWhen(AlternativeDefinition $alternative): string
    {
        switch ($alternative->getFallback()->getType()) {
            case FallbackDefinition::TYPE_VIRTUAL:
                return 'user already has a package that satisfy `{{FALLBACK_NAME}}`';
            case FallbackDefinition::TYPE_POLYFILL:
                $reqs = $alternative->getFallback()->getPreferredPackage()->getRequirements();
                if (count($reqs) !== 1) {
                    throw new \LogicException('Not implemented');
                }
                if (isset($reqs['php'])) {
                    return 'user has the right version of php';
                }
                if (\array_values($reqs)[0] === '*') {
                    if (\strpos(\array_keys($reqs)[0], 'ext-') === 0) {
                        return 'user has the extension '.\array_keys($reqs)[0];
                    }

                    return 'user has `{{FALLBACK_NAME}}`';
                }
                if (\strpos(\array_keys($reqs)[0], 'ext-') === 0) {
                    return 'user has the right version of the extension '.\array_keys($reqs)[0];
                }

                return 'user has the right version of `{{FALLBACK_NAME}}`';
            default:
                throw new \LogicException('Not implemented');
        }
    }

    private function getSolution(AlternativeDefinition $alternative): string
    {
        switch ($alternative->getFallback()->getType()) {
            case FallbackDefinition::TYPE_VIRTUAL:
                return 'users will be able to require their preferred implementation';
            case FallbackDefinition::TYPE_POLYFILL:
                return 'users will provide the native implementation';
            default:
                throw new \LogicException('Not implemented');
        }
    }

    private function getAlternatives(AlternativeDefinition $alternative): string
    {
        /** @var AlternativeDefinition[] $alternatives */
        $alternatives = \iterator_to_array($alternative->getFallback()->getAlternatives());
        if (count($alternatives) < 2) {
            return '';
        }

        $as = [];
        foreach ($alternatives as $a) {
            if ($a->getName() === $alternative->getName()) {
                continue;
            }
            $as[] = ' - ['.$a->getPackageName().'](https://github.com/'.$a->getPackageFullName().')';
        }

        return '## Alternatives

'.\implode("\n", $as).'

';
    }

    private function getAlternativePackage(AlternativeDefinition $alternative): string
    {
        /** @var AlternativeDefinition[] $alternatives */
        $reqs = $alternative->getPackage()->getRequirements();
        if (count($reqs) !== 1) {
            throw new \LogicException('Not implemented');
        }

        return \array_keys($reqs)[0];
    }
}
