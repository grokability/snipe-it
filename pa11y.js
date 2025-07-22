const pa11y = require('pa11y');

pa11y('http://snipe-it.test', {
    standard: "WCAG2AA",
    level: "error",
    defaults: {
        "timeout": 500000,
        "wait": 2000,
        "ignore": [
            "WCAG2AA.Principle1.Guideline1_4.1_4_3.G18",
            "WCAG2AA.Principle1.Guideline1_4.1_4_3.G18.Fail",
        ],
        "viewport": {
            "width": 1280,
            "height": 1024
        },
    },
    actions: [
        'set field #username to admin',
        'set field #password to password',
        'click element #submit',
        'wait for path to be /',
    ]
});