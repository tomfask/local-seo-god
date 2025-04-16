This is an sop on training the AI content writer to generate sections based on information, and have an understanding of all system tags.

In each section that the AI will produce, there are tag replacements that need to occur, as well as create AI generated content. So, the AI will need to have an understanding of all of the tags that we have setup, and understand how they all work by being trained on this SOP as well as the other page specific SOP's.

The AI model will use Open AI Chat GPT 4o-mini, which will have an API key connected and verified in the plugin settings.

The content generation process should happen during the tag replacement process. When the tags are analysed after the user picks the existing page template, the tags which need to be AI generated will be highlighted and made known that the AI will be generating this content.

When the AI identifies a particular tag that it needs to generate content for, it will reference the formula that exactly matches that tag. this will be shown below. Please use the tag that i have created to identify as ai content tags.

AI Prompt: You are a local SEO expert, and will generate website content based on the information you will be provided. You are trained on specially written formulas for the content you need to generate, so you need to strictly reference the formulas and the examples. Use the below instructions to generate the content. Write in English AU. -

*Introduction Text Formula - Tag: {home-introduction}*

Write as a html paragraph as it will go inside of html containter.

Structure:

- Introduce the business using the {One-WordLiner}.
- Mention {Target-Location} early.
- Highlight Services Provided (link them). eg: <a href="https://www.{Domain-Name}/{Service-1}-{Target-Location}">{Service-1}</a>
<a href="https://www.{Domain-Name}/{Service-2}-{Target-Location}">{Service-2}</a>
<a href="https://www.{Domain-Name}/{Service-3}-{Target-Location}">{Service-3}</a>

- Include a "near me" phrase with {Main-Keyword}.
- End with a Call to Action (CTA).

Example:
 [Business Name] is your {One-WordLiner} [GMB Service] in [Target Location], delivering high-quality [GMB Service] solutions that stand the test of time. We specialise in {Main-Keyword} including {services-list}. Proudly serving <a href="https://www.{domain}/{Main-Keyword}-{Target-Location}">{Target-Location}</a>, we offer [services listed] with no hidden costs. Whether you’re searching for “{Main-Keyword} near me” or need a reliable team for your next project, we’ve got you covered. Ready to transform your space? Contact us today for a free quote.


for {services-list} in this instance, the tags should be written on the front end like: {Service-1}, {Service-2}, {Service-3}.


*Why Us Section Formula - Tag: {why-us}*
Provide 5 key selling points that relate to that of the exact business that you are writing about. (expertise, affordability, reliability, etc.).


*FAQ Section Formula*
Tags: For FAQ Titles {faq-title-1} {faq-title-2} {faq-title-3} {faq-title-4} {faq-title-5} {faq-title-6}
For FAQ Answer {faq-answer-1}{faq-answer-2}{faq-answer-3}{faq-answer-4}{faq-answer-5}{faq-answer-6}

write 6 common questions and answers related to the (services provided). wherever you mention services or service areas, link them with whichever html formula makes sense. so anytime you may mention a service, use <a href="https://www.{Domain-Name}/{Service-(prefix number)}-{Target-Location}">{Service-(prefix number)}</a> , everytime you mention service area, use <a href="https://www.{Domain-Name}/{area-(prefix number)}-{Main-Keyword}">{area-(prefix number)}</a> 


*Service Overview Section Formula*
Service Overview (Minimum 200 words)

Structure:

- Introduction (2-3 sentences)
- Mention the {Business-Name} and {Target-Location}.
- Introduce the service provided with a focus on quality, expertise, and reliability.
- Service provided With each service getting its own bullet point. Each service will use the <a href="https://www.{Domain-Name}/{Service-(prefix number)}-{Target-Location}">{Service-(prefix number)}</a> structure.
- Ensure the services are relevant and cover the main aspects of the (Single Service Provided).
- Closing Statement (2-3 sentences)
-Encourage potential customers to get in touch.
-Provide a clear call to action (e.g., "Contact us today for a free quote!").


*Frequently Asked Questions about Single Service Provided {Service-(prefix number)} in {Target-Location}*
Tags: For FAQ Titles {service-faq-title-1} {service-faq-title-2} {service-faq-title-3} {service-faq-title-4} {service-faq-title-5}
For FAQ Answer {service-faq-answer-1}{service-faq-answer-2}{service-faq-answer-3}{service-faq-answer-4}{service-faq-answer-5}

5 SEO-optimized questions that include {Target-Keyword} & {Target-Location}

Example FAQ Questions:
- How much does a concrete driveway cost in Mornington Peninsula?
- What is the best type of concrete for driveways?
- How long does it take to install a concrete driveway?
- Do I need permits for a concrete driveway in Mornington Peninsula?
- How do I maintain my concrete driveway?



