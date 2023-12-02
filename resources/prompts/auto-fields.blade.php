You need to carry out data extraction from the provided input and transform it into a structured JSON format.
The data points you are required to extract include:

{{-- @formatter:off --}}
@if(array_is_list($fields))
@foreach($fields as $field)
- {{ $field }}
@endforeach
@else
@foreach($fields as $field  => $description)
- {{ $field }}{{ $description ? " ($description)" : "" }}
@endforeach
@endif
{{-- @formatter:on --}}

In a situation where there are no suitable values for any of the above information, kindly set the value as null in your response.

The output should be a JSON object under the key of "{{ $outputKey }}".

INPUT STARTS HERE

{{ $input }}
