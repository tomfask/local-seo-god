The following sop is instructions about how the bulk page generation will function.

1. After choosing the zeus mode option, The plugin will get the user to choose which wordpress page they would like to generate their multiple pages from.

2. The user selects from a dropdown which bulk formula they will use. (eg: if they want to generate bulk service pages, they will pick the service pages option, which will then use the service-page-bulk.md as the formula and instruction to produce the bulk pages). The dropdowns will be the following: 'Service|TargetLocation' (with example of "Concrete Driveways Melbourne), then 'MainKeyword|ServiceArea' (with example of "Concreter Mount Martha"), then 'Service|ServiceArea' (eg: "Concrete Driveways Mount Martha")

3. depending on which bulk formula is chosen, the plugin will then understand what information it needs to gather. It will use the business information provided in the plugin settings with their respective tags to provide a breakdown of the pages it is required to make. below is a breakdown:

'Service|TargetLocation' pages: If it needs to generate service pages, it will prioritise the services provided (tag {Service-(prefix number)}) in the business information to understand that it has to create x amount of pages per each service listed. Then it will use the service-page-bulk.md for the page and tag formula

'MainKeyword|ServiceArea' pages: when generating service area pages, it will prioritise and gather the service areas provided (the tag is {area-(prefix number)}) in the business information section to understand that is has to create x amount of pages per each service area provided. then it will use the Locations-page-bulk.md for the page and tag formula.

'Service|ServiceArea' pages: when generating the area specific service pages, it will priorities and gather services provided (tag {Service-(prefix number)}) as well as gather the service areas provided (the tag is {area-(prefix number)}) in the business information to understand that it has to create x amount of pages per each service x service area listed. Then it will use the area-specific-service-page-bulk.md for the page and tag formula. with this formula in particular, the plugin has to understand that it needs to create a page for each service inside of each service area. EG: if there are 2 service areas provided, and 4 services provided.... the amount of total pages will be 8. That would be service area x service, until combinations are exhausted.

4. Once the plugin has listed the pages it needs to create, the user will be able to click Generate, where the plugin will now begin bulk producing pages. 
!IMPORTANT! - The plugin needs to understand that each individual page will use each specific service and service area depending on what formula is chosen. for example, if we are making service pages and there are 10 services listed, each page uses the individual service tag (service-1, service-2. etc) to create each individual page. Each page title will need to be dynamically tagged to each different service or service area too. (the formula will be shown in each respected sop file for the different types)

5. The final outcome overview step. This is where the user can see a list of pages created, along with their custom title name created too. They will then hit the finish button to refresh the process and take them page to the mode option selection of single page or bulk.

