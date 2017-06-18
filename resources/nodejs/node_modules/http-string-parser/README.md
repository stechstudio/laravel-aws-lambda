# http-string-parser

[![NPM Version](https://img.shields.io/npm/v/http-string-parser.svg)](https://www.npmjs.com/package/http-string-parser)
[![Build Status](https://travis-ci.org/apiaryio/http-string-parser.png)](https://travis-ci.org/apiaryio/http-string-parser)
[![Dependency Status](https://david-dm.org/apiaryio/http-string-parser.png)](https://david-dm.org/apiaryio/http-string-parser)
[![devDependency Status](https://david-dm.org/apiaryio/http-string-parser/dev-status.png)](https://david-dm.org/apiaryio/http-string-parser#info=devDependencies)

Parse HTTP messages (Request and Response) from raw string in Node.JS

## Parse HTTP Messages
```javascript
var parser = require('http-string-parser');

request = parser.parseRequest(requestString);
response = parser.parseResponse(responseString);

console.log(request);
console.log(response);
```

See more about [Request][request] and [Response][response] data model.

[request]: https://www.relishapp.com/apiary/gavel/docs/data-model#http-request
[response]: https://www.relishapp.com/apiary/gavel/docs/data-model#http-response

## API Reference

`parseRequest(requestString)`

`parseRequestLine(requestLine)`

`parseResponse(responseString)`

`parseStatusLine(statusLine)`

`parseHeaders(headersLinesArray)`

- - - 

NOTE: Proof of concept, naive HTTP parsing, wheel re-inventation. In future it may be replaced with better parser from [Node.JS core's C bindings of NGINX HTTP parser](https://github.com/joyent/http-parser) or [PEG.js HTTP parser](https://npmjs.org/package/http-pegjs)

