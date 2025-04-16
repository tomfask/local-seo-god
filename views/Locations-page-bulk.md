SOP for Location Page Creation

URL structure- https://www.{Domain-Name}/{area-(prefix number)}-{Main-Keyword}

page title formula - {One-WordLiner} {Main-Keyword} {area-(prefix number)} | {GMB-Service} Near Me
!important - in the page formula remember {area-(prefix number)} is dynamic, so each service area will have its own dynamic tag (area-1, area-2 etc)
- The {One-WordLiner} needs to be randomised in every page title created so it is not the same.

!important - the location (service area) pages will specifically focus on the one service area per page. So if the page is about {area-5}, the page tags where it mentions {area-(prefix number)} should all dynamically be {area-5}. EXCEPTION- There will be a list of service areas in a section of the page. these will be dynamically replaced with a html formula that hyperlinks each service area in the one html block. see below for instructions.

-----------------

This is a list of tags specifically for service area page word replacement. any time the tag is visible in the page, including page title and meta data, the tag will be replaced.

{One-WordLiner}
{Main-Keyword}
{area-(prefix number)} - (eg: area-1, area-2 etc)
{GMB-Service}
{Service-(prefix number)} - (eg: {Service-1} {Service-2} {Service-3} {Service-4})
{Business-Name}



for {service-area-list} tag, you need to insert html of each of the service areas that wrap with an internal link. here is the formula: 
<a href="https://www.{Domain-Name}/{area-1}-{Main-Keyword}">{area-1}</a>
<a href="https://www.{Domain-Name}/{area-2}-{Main-Keyword}">{area-2}</a>
<a href="https://www.{Domain-Name}/{area-3}-{Main-Keyword}">{area-3}</a>
This is 3 area tags, but the formula will generate as many as it needs to in relation to the amount of service areas there are in the business information.
