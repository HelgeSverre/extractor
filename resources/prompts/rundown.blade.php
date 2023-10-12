This is a rundown for an event, extract the following information from the rundown and return it as valid JSON:

- eventName
- startDate (nullable)
- endDate (nullable, leave blank if not specified)
- description (text)
- notes (text)
- columns[] (array of strings)
- sessions
    - sessionNumber
    - sessionName
    - notes
    - sessionItems
    - number
    - sessionRows
        - notes
        - startTime
        - duration
        - columns [array of objects with columnName and columnValue]

RUNDOWN:

{{ $input }}

OUTPUT IN JSON:
