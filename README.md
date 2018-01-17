## Adding components
1. implement child of class Components
2. add your class to the autoloader section of extension.json
3. in ComponentLibrary, look for method `rawComponentsDefinition` and add all necessary data
    for the new component
4. create all necessary message entries in qqq.json and en.json.
5. add tests for your new class
`
## Format of data array in ComponentLibrary::rawComponentsDefinition
```php
    [
        (string><component name, same as in getComponentName()> => [
            'class' => (string)'\\Bootstrap\\Components\\<Component Class>',
            'handlerType' => (string)<self::HANDLER_TYPE_PARSER_FUNCTION or self::HANDLER_TYPE_TAG_EXTENSION>,
            'attributes' => [
                'default' => (bool)true|false <does this component allow the default attributes>
                (string)... <list of individual attributes, must be registered in AttributeManager> 
            ],
            'modules' => [
                'default' => (string|array)<modules to load when this component is parsed>
                'skin' => (string|array)<modules to load when this component is parsed and "skin" is active>
            ]
        ],
    ];
```
## abstract methods to implement in new class
```php
public function placeMe( ParserRequest $parserRequest ) {
    ...
    return <(array|string) your component html code>
}
```

## Remarks for the image modal
* when you use tidy, you cannot place image modals inside definition lists (using either the html code or wiki markup ; and :)