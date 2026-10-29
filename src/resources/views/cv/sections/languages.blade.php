{{-- [{language, reading, writing, speaking}] with High / Medium / Low --}}
<table class="cv-table cv-table--narrow">
    <thead>
        <tr>
            <th scope="col">{{ $cvView->word('language') }}</th>
            <th scope="col">{{ $cvView->word('reading') }}</th>
            <th scope="col">{{ $cvView->word('writing') }}</th>
            <th scope="col">{{ $cvView->word('speaking') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $l)
            <tr class="cv-item">
                <td>{{ $l['language'] }}</td>
                <td>{{ $l['reading'] ?? '' }}</td>
                <td>{{ $l['writing'] ?? '' }}</td>
                <td>{{ $l['speaking'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
