/*jslint node: true */

const spawn = require("child_process").spawnSync;
const parser = require("http-string-parser");

exports.handler = function(event, context) {
    // Sets some sane defaults here so that this function doesn't fail
    // when it's not handling a HTTP request from API Gateway.
    let requestMethod = event.httpMethod || 'GET';
    let requestBody = event.body || '';
    let serverName = event.headers ? event.headers.Host : '';
    let requestUri = event.path || '';
    let headers = {};
    let queryParams = '';

    // Convert all headers passed by API Gateway into the correct format for PHP CGI.
    // This means converting a header such as "X-Test" into "HTTP_X-TEST".
    if (event.headers) {
        Object.keys(event.headers).map(function (key) {
            headers['HTTP_' + key.toUpperCase().replace(/-/g, '_')] = event.headers[key];
            headers[key.toUpperCase().replace(/-/g, '_')] = event.headers[key];
        });
    }

    // Convert query parameters passed by API Gateway into the correct format for PHP CGI.
    if (event.queryStringParameters) {
        let parameters = Object.keys(event.queryStringParameters).map(function(key) {
            let obj = key + "=" + event.queryStringParameters[key];
            return obj;
        });
        queryParams = parameters.join("&");
    }

    // Spawn the PHP CGI process with a bunch of environment variables that describe the request.
    let php = spawn('../bin/php-cgi', ['../../public/index.php'], {
        env: Object.assign({
            REDIRECT_STATUS: 200,
            REQUEST_METHOD: requestMethod,
            SCRIPT_FILENAME: '../../public/index.php',
            SCRIPT_NAME: '/index.php',
            PATH_INFO: '/',
            SERVER_NAME: serverName,
            SERVER_PROTOCOL: 'HTTP/1.1',
            REQUEST_URI: requestUri,
            QUERY_STRING: queryParams,
            AWS_LAMBDA: true,
            CONTENT_LENGTH: Buffer.byteLength(requestBody, 'utf-8')
        }, headers, process.env),
        input: requestBody
    });

    // When the process exists, we should have a complete HTTP response to send back to API Gateway.
    let parsedResponse = parser.parseResponse(php.stdout.toString('utf-8'));

    // Signals the end of the Lambda function, and passes the provided object back to API Gateway.
    context.succeed({
        statusCode: parsedResponse.statusCode || 200,
        headers: parsedResponse.headers,
        body: parsedResponse.body
    });
};