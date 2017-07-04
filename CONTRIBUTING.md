# Bug Reports

To encourage active collaboration, we strongly encourages **pull requests**, not just bug reports. *"Bug reports"* may also be sent in the form of a pull request containing a failing test.

If you open an issue, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of an issue is to make it easy for yourself - and others - to replicate the bug and develop a fix.

Remember, issues are opened in the hope that others with the same problem will be able to collaborate with you on solving it. Do not expect that the issue will automatically see any activity or that others will jump to fix it. Opening an issue serves to help yourself and others start on the path of fixing the problem.

# Coding Style

We follow the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standard and the [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) autoloading standard.

## PHPDoc
Below is an example of a valid documentation block. Note that the @param attribute is followed by two spaces, the argument type, two more spaces, and finally the variable name:

```php
/**
 * Register a binding with the container.
 *
 * @param  string|array  $abstract
 * @param  \Closure|string|null  $concrete
 * @param  bool  $shared
 * @return void
 */
public function bind($abstract, $concrete = null, $shared = false)
{
    //
}
```