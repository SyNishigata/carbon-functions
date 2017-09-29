All calculations are done in the following files:

*Food*
emissions-food.php //starting at line 195
*Home*
emissions-electric.php //starting at line 189
emissions-fuel.php //starting at line 189
emissions-gas.php //starting at line 188
emissions-water.php //starting at line 181
*Travel*
emissions-bus.php //starting at line 196
emissions-car.php //starting at line 323
emissions-motorcycle.php //starting at line 242
emissions-plane.php //strating at line 189
emissions-train.php //starting at line 197
*Waste*
emissions-waste.php //starting at line 164





These files have been corrected by me to match excel spreadsheet:

*emissions-gas.php // lines 200-218 changed to lines 219-235
*emissions-water.php // lines 197 and 203 changed to lines 199 and 205




These files I can't confirm their accuracy because don't match categories on the excel spreadsheet:

*emissions-food.php // spreadsheet combines food types and they are all measured by gCO2e/calorie
		       but our website calculates by how many times a week a person eats these foods
*emissions-fuel.php // spreadsheet's "Other Fuels" is measured by gCO2e/US$, our website calculates
		       by yearly gallon consumption
*emissions-gas.php // spreadsheet doesn't account for "kWh" unit measurement
*emissions-motorcycle.php // spreadsheet doesn't have motorcycle calculations
*emissions-waste.php // spreadsheet doesn't have waste calculations




These specific sections in spreadsheet don't correlate with the website http://www.coolcalifornia.org/ :

*emissions-food.php // the website uses an "average" scale and excel spreadsheet combines food types
*emissions-electric.php // website: 99999 kwh/y = 64.31 co2, spreadsheet: 99999kwh = 91.01 co2
*emissions-fuel.php // website: 1000 us$/year = 4.95 co2, spreadsheet says 1000usd = 0.68 co2. also 
		       our website calculates by gallons, not us$
*emissions-bus.php // website: 99999 miles/year = 0.14 co2, spreadsheet says 99999m = 37.80 co2




*My Report Summary*

Any file that was not listed above has been tested by me to both match the excel spreadsheet calculations
and match the results from the website http://www.coolcalifornia.org/


