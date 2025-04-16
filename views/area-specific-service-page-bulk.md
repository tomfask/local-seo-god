SOP for Area specific Service Page Creation


URL structure- https://www.{Domain-Name}/{Service-(prefix number)}-{area-(prefix number)}

page title formula - {Service-(prefix number)} {area-(prefix number)} | {GMB-Service} Near Me

!important - in the page formula remember {Service-(prefix number)} is dynamic, so each service area will have its own dynamic tag (service-1, service-2 etc)
AND {area-(prefix number)} is dynamic, so each service area will have its own dynamic tag (area-1, area-2 etc)


!important - the area specific services pages will focus on one service and one service area per page. So if the page is about {service-5} and {area-2}, the page tags where it mentions {Service-(prefix number)} or {area-(prefix number)} should all dynamically be replaced with {service-5} or {area-2,}, and then replaced with the corresponding service or service area. EXCEPTION- There will be a list of area specific services in a section of the page. these will be dynamically replaced with a html formula that hyperlinks each service area in the one html block. see below for instructions.

-----------------

This is a list of tags specifically for services page word replacement. any time the tag is visible in the page, including page title and meta data, the tag will be replaced.
{Service-(prefix number)} - (eg: {Service-1} {Service-2} {Service-3} {Service-4})
{area-(prefix number)} - (eg: {area-1} {area-2} {area-3} {area-4})
{GMB-Service}
{Business-Name}
{One-WordLiner}
{Main-Keyword}



for {area-specific-services-list} tag, you need to insert html of each of the area specific services that wrap with an internal link. here is the formula: 
<a href="https://www.{Domain-Name}/{Service-1}-{area-(prefix number)}">{Service-1} {area-(prefix number)}</a>
<a href="https://www.{Domain-Name}/{Service-2}-{area-(prefix number)}">{Service-2} {area-(prefix number)}</a>
<a href="https://www.{Domain-Name}/{Service-3}-{area-(prefix number)}">{Service-3} {area-(prefix number)}</a>

This is 3 service tags, but the formula will generate as many as it needs to in relation to the amount of services there are in the business information.
*note* the {Service-(prefix number)} inside of the html will be that of whatever the main {area-(prefix number)} of the page is. so similar to my pprevious example above, if the page is about {area-2}, then {area-(prefix number)} inside of the html links will be replaced with {area-2} and then replaced with the corresponding service area.