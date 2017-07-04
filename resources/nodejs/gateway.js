/*jslint node: true */

const spawn = require("child_process").spawnSync;
const parser = require("http-string-parser");
var path = require("path");

exports.handler = function(event, context) {
    // Sets some sane defaults here so that this function doesn't fail
    // when it's not handling a HTTP request from API Gateway.
    var requestMethod = event.httpMethod || 'GET';
    var requestBody = event.body || '';
    var serverName = event.headers ? event.headers.Host : 'lambda_test.dev';
    var requestUri = event.path || '';
    var headers = {};
    var queryParams = '';

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
        var parameters = Object.keys(event.queryStringParameters).map(function(key) {
            var obj = key + "=" + event.queryStringParameters[key];
            return obj;
        });
        queryParams = parameters.join("&");
    }

    // Spawn the PHP CGI process with a bunch of environment variables that describe the request.
    var scriptPath = path.resolve(__dirname + '/../../public/index.php')

    var php = spawn('php-cgi', ['-f', scriptPath], {
        env: Object.assign({
            REDIRECT_STATUS: 200,
            REQUEST_METHOD: requestMethod,
            SCRIPT_FILENAME: scriptPath,
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

    // When the process exists, we should have a compvare HTTP response to send back to API Gateway.
    var parsedResponse = parser.parseResponse(php.stdout.toString('utf-8'));

    // Signals the end of the Lambda function, and passes the provided object back to API Gateway.
    context.succeed({
        statusCode: parsedResponse.statusCode || 200,
        headers: parsedResponse.headers,
        body: parsedResponse.body
    });
};