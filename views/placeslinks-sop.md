Standard Operating Procedure (SOP) for Linking Geographic Locations in Wikipedia Articles

1. Understanding Wikipedia’s Geographic Link Structure
	•	Wikipedia replaces spaces with underscores (_) in URLs but uses spaces in article links.
	•	Example:
	•	URL: https://en.wikipedia.org/wiki/Mornington,_Victoria
	•	Wikipedia Link Format: [[Mornington, Victoria]]

⸻

2. General Link Structures for Geographic Locations

Cursor AI should construct links based on these possible Wikipedia link formats for different types of places.

a) Standard Place Name Links

For single-word or unique place names:
	•	URL: https://en.wikipedia.org/wiki/Melbourne
	•	Wikipedia Link Format: [[Melbourne]]

⸻

b) Place Name with a Comma & Region

For places that require disambiguation, typically a city, suburb, or town with its state, region, or country:
	•	URL: https://en.wikipedia.org/wiki/Mornington,_Victoria
	•	Wikipedia Link Format: [[Mornington, Victoria]]
	•	Other Examples:
	•	[[Los Angeles, California]] → https://en.wikipedia.org/wiki/Los_Angeles,_California
	•	[[London, Ontario]] → https://en.wikipedia.org/wiki/London,_Ontario

⸻

c) Country or Region Name Alone

When linking to a country, use the country’s full name:
	•	URL: https://en.wikipedia.org/wiki/Australia
	•	Wikipedia Link Format: [[Australia]]

For regions or states within a country:
	•	URL: https://en.wikipedia.org/wiki/Victoria_(Australia)
	•	Wikipedia Link Format: [[Victoria (Australia)]]

⸻

d) Place Name with a Parenthetical Disambiguation

For locations that share a name with other places or meanings:
	•	URL: https://en.wikipedia.org/wiki/Paris_(Texas)
	•	Wikipedia Link Format: [[Paris (Texas)]]
	•	Other Examples:
	•	[[Springfield (Illinois)]] → https://en.wikipedia.org/wiki/Springfield_(Illinois)
	•	[[Newcastle (Australia)]] → https://en.wikipedia.org/wiki/Newcastle,_New_South_Wales

⸻

e) Place Name with a Shortened Display Name (Piped Links)

When a shorter version of a place name is needed while still linking to the full article:
	•	URL: https://en.wikipedia.org/wiki/New_York_City
	•	Wikipedia Link Format: [[New York City|New York]]
	•	Other Examples:
	•	[[Los Angeles, California|Los Angeles]] → Displays as Los Angeles but links to the full page.
	•	[[Mornington, Victoria|Mornington]] → Displays as Mornington but links to the correct page.

⸻

3. Guidelines for Choosing the Correct Link Format
	•	For major cities or well-known places: Use [[Place]] (e.g., [[Sydney]], [[London]]).
	•	For locations with multiple versions: Use [[Place, Region]] (e.g., [[Portland, Oregon]] vs. [[Portland, Maine]]).
	•	For places with disambiguation pages: Use [[Place (Region)]] (e.g., [[Cambridge (England)]]).
	•	For clarity in articles: Use piped links to simplify readability ([[New York City|New York]]).

    
  4. example links  
  [[Melbourne]] → https://en.wikipedia.org/wiki/Melbourne  
[[Los Angeles, California]] → https://en.wikipedia.org/wiki/Los_Angeles,_California  
[[Springfield (Illinois)]] → https://en.wikipedia.org/wiki/Springfield_(Illinois)  
[[Mornington, Victoria]] → https://en.wikipedia.org/wiki/Mornington,_Victoria  
[[Victoria (Australia)]] → https://en.wikipedia.org/wiki/Victoria_(Australia)  
[[United States]] → https://en.wikipedia.org/wiki/United_States  
[[Midwest (United States)]] → https://en.wikipedia.org/wiki/Midwest_(United_States)  
[[New York City|New York]] → https://en.wikipedia.org/wiki/New_York_City  