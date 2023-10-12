You need to extract all the contacts from the provided text and transform it into a structured JSON format.
The data points you are required to extract include:

@foreach($config["fields"] as $field => $description)
- {{ $field }} ({{ $description }})
@endforeach

In a situation where there are no suitable values for any of the above information, set the value as null in your response.
The output should be a flat list of JSON objects.

{{ $input }}

OUTPUT IN JSON
