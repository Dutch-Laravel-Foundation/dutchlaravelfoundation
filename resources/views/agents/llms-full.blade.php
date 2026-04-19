# Dutch Laravel Foundation

> {{ $preamble }}

This file contains full content of curated collections. For a lighter index with links only, see [{{ $base }}/llms.txt]({{ $base }}/llms.txt).

@foreach ($sections as $sectionTitle => $items)

---

# {{ $sectionTitle }}

@foreach ($items as $entry)

---

{!! $renderer->render($entry) !!}
@endforeach
@endforeach
