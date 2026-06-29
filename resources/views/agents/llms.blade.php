# Dutch Laravel Foundation

> {{ $preamble }}

## About
@foreach ($highlighted as $page)
- [{{ $page['label'] }}]({{ $base }}/{{ ltrim($page['slug'], '/') }}.md)
@endforeach

## Knowledge Base
@foreach ($knowledgeItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md)
@endforeach

## Insights
@foreach ($insightsItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md){!! $item['date'] ? ' — ' . $item['date'] : '' !!}
@endforeach

## Events
@foreach ($eventsItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md){!! $item['date'] ? ' — ' . $item['date'] : '' !!}
@endforeach

## Internships
@foreach ($internshipItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md)
@endforeach

## Markdown access
- Append `.md` to any content URL, or send `Accept: text/markdown`
- Full dump of content: {{ $base }}/llms-full.txt
