<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} — Notion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 font-sans" x-data="{ sidebarOpen: window.innerWidth >= 768 }">

<div class="flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <aside class="flex-shrink-0 bg-gray-50 border-r border-gray-200 flex flex-col transition-all duration-200"
           :class="sidebarOpen ? 'w-60' : 'w-0 overflow-hidden'">
        <div class="p-3 border-b border-gray-200 flex items-center justify-between">
            <span class="font-semibold text-sm text-gray-700">{{ config('app.name') }}</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-2">
            @foreach ($pages as $p)
                <a href="{{ route('pages.show', $p->id) }}"
                   class="flex items-center gap-2 px-3 py-1.5 text-sm rounded-md mx-1 transition
                          {{ $p->id == $page->id ? 'bg-gray-200 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <span>{{ $p->icon }}</span>
                    <span class="truncate">{{ $p->title }}</span>
                    @if($p->type === 'database')
                        <span class="ml-auto text-xs text-gray-400">DB</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="p-3 border-t border-gray-200">
            <button class="flex items-center gap-2 text-xs text-gray-500 hover:text-gray-700">
                <span>＋</span> New page
            </button>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        {{-- Topbar --}}
        <header class="flex items-center gap-3 px-4 py-2 border-b border-gray-200 bg-white">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 text-lg">☰</button>
            <span class="text-sm text-gray-500 truncate">{{ $page->icon }} {{ $page->title }}</span>
        </header>

        {{-- Cover --}}
        <div class="h-24 {{ $page->cover_color }} flex-shrink-0"></div>

        {{-- Page body --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-3xl mx-auto px-8 py-6">
                <h1 class="text-4xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                    <span class="text-3xl">{{ $page->icon }}</span>
                    {{ $page->title }}
                </h1>

                @if ($page->type === 'page')
                    <div class="prose prose-gray max-w-none">
                        @php
                            $lines = explode("\n", $page->content ?? '');
                        @endphp
                        @foreach ($lines as $line)
                            @if (str_starts_with($line, '## '))
                                <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-2">{{ substr($line, 3) }}</h2>
                            @elseif (str_starts_with($line, '# '))
                                <h1 class="text-2xl font-bold text-gray-900 mt-6 mb-3">{{ substr($line, 2) }}</h1>
                            @elseif (str_starts_with($line, '- '))
                                <li class="ml-4 text-gray-700 mb-1">{{ substr($line, 2) }}</li>
                            @elseif (str_starts_with($line, '**') && str_ends_with(trim($line), '**'))
                                <p class="font-semibold text-gray-800 mt-3">{{ trim($line, '*') }}</p>
                            @elseif (str_starts_with($line, '```') || $line === '```')
                                <div class="bg-gray-100 rounded px-4 py-2 font-mono text-xs text-gray-700 my-2 whitespace-pre-wrap">{{-- code block --}}</div>
                            @elseif (str_starts_with($line, '---'))
                                <hr class="my-4 border-gray-200">
                            @elseif (str_starts_with($line, '*'))
                                <p class="text-gray-400 text-xs italic mt-2">{{ trim($line, '*') }}</p>
                            @elseif (strlen(trim($line)) > 0)
                                <p class="text-gray-700 mb-2 leading-relaxed">{{ $line }}</p>
                            @else
                                <div class="h-2"></div>
                            @endif
                        @endforeach
                    </div>

                @else
                    {{-- Database view --}}
                    <div class="mb-4 flex flex-wrap gap-2 items-center">
                        <form method="GET" action="{{ route('pages.show', $page->id) }}" class="flex flex-wrap gap-2">
                            @foreach ([['status','Status',['not_started','in_progress','done','cancelled']],['priority','Priority',['low','medium','high']]] as [$field,$label,$opts])
                            <select name="{{ $field }}" onchange="this.form.submit()"
                                    class="text-sm border-gray-300 rounded-md shadow-sm py-1 px-2">
                                <option value="">All {{ $label }}</option>
                                @foreach ($opts as $o)
                                    <option value="{{ $o }}" @selected(request($field)===$o)>{{ ucfirst(str_replace('_',' ',$o)) }}</option>
                                @endforeach
                            </select>
                            @endforeach
                            <select name="assignee" onchange="this.form.submit()"
                                    class="text-sm border-gray-300 rounded-md shadow-sm py-1 px-2">
                                <option value="">All Assignees</option>
                                @foreach ($assignees as $a)
                                    <option value="{{ $a }}" @selected(request('assignee')===$a)>{{ $a }}</option>
                                @endforeach
                            </select>
                            @if(request()->hasAny(['status','assignee','priority']))
                                <a href="{{ route('pages.show', $page->id) }}" class="text-xs text-gray-500 hover:text-gray-700 self-center">Clear</a>
                            @endif
                        </form>
                        <span class="ml-auto text-xs text-gray-400">{{ $rows->count() }} rows</span>
                    </div>

                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full text-sm divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @foreach (['Name','Status','Priority','Assignee','Due Date','Tags'] as $h)
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($rows as $row)
                                    @php
                                        $sc = ['not_started'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-100 text-blue-700','done'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-600'][$row->status] ?? 'bg-gray-100 text-gray-600';
                                        $pc = ['low'=>'bg-gray-100 text-gray-500','medium'=>'bg-yellow-100 text-yellow-700','high'=>'bg-red-100 text-red-700'][$row->priority] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 font-medium text-gray-800 whitespace-nowrap">{{ $row->title }}</td>
                                        <td class="px-4 py-2.5 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">{{ ucfirst(str_replace('_',' ',$row->status)) }}</span></td>
                                        <td class="px-4 py-2.5 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $pc }}">{{ ucfirst($row->priority) }}</span></td>
                                        <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ $row->assignee }}</td>
                                        <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('M d') : '-' }}</td>
                                        <td class="px-4 py-2.5 whitespace-nowrap">
                                            @if($row->tags)
                                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs">{{ $row->tags }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No rows found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

</body>
</html>
