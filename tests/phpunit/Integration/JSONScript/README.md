## TestCases
Contains X files with a total of X tests:

* [accordion-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/accordion-01.json) Test accordion component
* [alert-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/accordion-01.json) Test alert component
* [badge-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/accordion-01.json) Test badge component
* [button-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/accordion-01.json) Test button component
* [carousel-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/accordion-01.json) Test carousel component
* [icon-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/icon-01.json) Test icon component
* [image_modal-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/image-modal-01.json) Test placing modals instead of image tags
* [image_modal-02.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/image-modal-02.json) Test image modals with invalid thumb image
* [well-01.json](https://github.com/oetterer/BootstrapComponents/tree/master/tests/phpunit/Integration/JSONScript/TestCases/well-01.json) Test well component

## Writing a test case

### Assertions

Integration tests aim to prove that the "integration" between MediaWiki
and the extension works at a sufficient level therefore assertion
may only check or verify a specific part of an output to avoid that
system information (DB ID, article url etc.) distort to overall test results.

### Add a new test case

- Follow the `alert-01.json` example on how to structure the JSON file (setup,
  test etc.)
- You can find an example for image upload in `carousel-01.json`.
- You can add templates the same way as the Target page in `button-01.json`.
  Just provide an appropriate content.