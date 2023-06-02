# Welcome to our recruitment process :)

## Background

Repository you see contains simple application for creating, answering and reporting simple surveys.
Let's say it's MVP with multiple design/technical/performance problems which causes maintenance and development difficult.
Down below, you can find information about how to spin up development environment and cURL commands for calling existing endpoints.

## TASK

You need to deal with some problems around closing survey:
* survey can go live once and be closed once - there shouldn't be an option to reopen closed survey nor set the initial state
* our mailer fails very often which makes the close process very unstable
* generating reports for surveys with huge number of answers takes too long and sometimes fails due to memory issues
* it's hard to write reliable and efficient tests for this process


Your task is to address these problems (of course don't hesitate to solve other issues).
You can change the HTTP contracts if you find it required.

Please show us some skills in these aspects:
* CQS/CQRS
* SOLID
* Clean Code
* Framework-agnostic
* REST principles
* Tests

It doesn't need to be a fully working code. Well documented draft will be fine.
We will just have a look at your coding style and ask about your decisions.
Please fork this repository and send us back the link to your fork.

## Business rules

* each survey has to have unique name
* initially survey is not live, to be able to answer, survey needs to go live
* every answer has one likert question about quality, which is required
* choosing Poor or Very Poor (-1 or -2) makes comment required as an explanation of choice
* only survey which didn't go live can be edited
* live survey can not be deleted
* once survey is closed, nobody can answer it
* a report is generated as soon as the survey is closed
* email with the information about generated report is being sent to the address provided during survey creation

## Development environment

Prerequisites:
* docker
* docker compose

To prepare your development environment you need to run these commands:
```
docker compose up -d
docker compose exec php-fpm composer install
docker compose exec php-fpm bin/console doctrine:migrations:migrate -n
```

Application will be available under `http://localhost`, MailCatcher under `http://localhost:81`.

## Existing endpoints

List all surveys:
```
curl -X GET --location "http://localhost/survey" \
    -H "Accept: application/json"
```

Create new survey:
```
curl -X POST --location "http://localhost/survey" \
    -H "Content-Type: application/json" \
    -d "{
            \"name\": \"survey name\",
            \"reportEmail\": \"test@example.com\"
        }"
```

Edit survey:
```
curl -X PUT --location "http://localhost/survey/{surveyId}" \
    -H "Content-Type: application/json" \
    -d "{
            \"name\": \"new name\",
            \"reportEmail\": \"test2@example.com\"
        }"
```

Delete survey:
```
curl -X DELETE --location "http://localhost/survey/{surveyId}" \
    -H "Content-Type: application/json"
```

Send survey live:
```
curl -X PUT --location "http://localhost/survey/{surveyId}/status" \
    -H "Content-Type: application/json" \
    -d "{
            \"status\": \"live\"
        }"
```

Close survey:
```
curl -X PUT --location "http://localhost/survey/{surveyId}/status" \
    -H "Content-Type: application/json" \
    -d "{
            \"status\": \"closed\"
        }"
```

Add neutral/positive answer:
```
curl -X POST --location "http://localhost/survey/{surveyId}/answer" \
    -H "Content-Type: application/json" \
    -d "{
            \"quality\": 0,
            \"comment\": null
        }"
```

Add negative answer:
```
curl -X POST --location "http://localhost/survey/{surveyId}/answer" \
    -H "Content-Type: application/json" \
    -d "{
            \"quality\": -2,
            \"comment\": "quality was very poor"
        }"
```

Show report:
```
curl -X GET --location "http://localhost/report/{reportId}" \
    -H "Accept: application/json"
```
