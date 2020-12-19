# Dynamo DB - Access pattern

## Example data
| PK                | SK                    | PK02 (GSI1)   |
| ----------------- | --------------------- | ------------- |
| en                | Locale                |               |
| p1                | Food                  |               |
| p2                | Food                  |               |
| p3                | Food                  |               |
| m1#01             | Month                 |               |
| m2#02             | Month                 |               |
| FoodMonth#m1#01   | en#name               | p1            |
| FoodMonth#m1#01   | en#name               | p2            |
| FoodMonth#m2#02   | en#name               | p2            |
| FoodMonth#m2#02   | en#name               | p3            | 

### Get all foods by month order by alphabetic

Query:  
````text
Table = VGI (PK#SK)
PK = FoodMonth#m1#01
SK BEGINS_WITH Product#en 
```` 

Result:  

| PK                | SK                    | PK02 (GSI1)   |  
| ----------------- | --------------------- | ------------- |  
| FoodMonth#m1#01   | en#name               | p2            |  
| FoodMonth#m1#01   | en#name               | p1            |  


### Get all months by product order by numeric value
Query:
````text
Table = VGI
Index = GSI1(PK02#PK)
PK = p2
SK BEGINS_WITH Month
```` 
Result:

| PK                | SK                    | PK02 (GSI1)   |  
| ----------------- | --------------------- | ------------- | 
| FoodMonth#m1#01   | en#name               | p2            |
| FoodMonth#m2#02   | en#name               | p2            |
