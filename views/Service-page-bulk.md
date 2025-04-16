SOP for Service Page Creation

URL structure- https://www.{Domain-Name}/{Service-(prefix number)}-{Target-Location}

page title formula - {Service-(prefix number)} {Target-Location} | {GMB-Service} Near Me

!important - in the page formula remember {Service-(prefix number)} is dynamic, so each service area will have its own dynamic tag (service-1, service-2 etc)


!important - the service pages will specifically focus on the one service per page. So if the page is about {service-5}, the page tags where it mentions {Service-(prefix number)} should all dynamically be {service-5}. EXCEPTION- There will be a list of services in a section of the page. these will be dynamically replaced with a html formula that hyperlinks each service area in the one html block. see below for instructions.

-----------------

This is a list of tags specifically for services page word replacement. any time the tag is visible in the page, including page title and meta data, the tag will be replaced.
{Service-(prefix number)} - (eg: {Service-1} {Service-2} {Service-3} {Service-4})
{Target-Location}
{GMB-Service}
{Business-Name}
{One-WordLiner}
{Main-Keyword}



for {services-list} tag, you need to insert html of each of the services that wrap with an internal link. here is the formula: 
<a href="https://www.{Domain-Name}/{Service-1}-{Target-Location}">{Service-1}</a>
<a href="https://www.{Domain-Name}/{Service-2}-{Target-Location}">{Service-2}</a>
<a href="https://www.{Domain-Name}/{Service-3}-{Target-Location}">{Service-3}</a>

This is 3 service tags, but the formula will generate as many as it needs to in relation to the amount of service areas there are in the business information.



