{{-- [{exam, concentration, institution, board, result_type, result, scale, passing_year, achievements[]}] — the BD table --}}
<table class="cv-table">
    <thead>
        <tr>
            <th scope="col">{{ $cvView->word('exam') }}</th>
            <th scope="col">{{ $cvView->word('group') }}</th>
            <th scope="col">{{ $cvView->word('institution') }}</th>
            @if ($cvView->showEducationBoardColumn())
                <th scope="col">{{ $cvView->word('board') }}</th>
            @endif
            <th scope="col">{{ $cvView->word('result') }}</th>
            <th scope="col">{{ $cvView->word('year') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $edu)
            <tr class="cv-item">
                <td>{{ $edu['exam'] ?? '' }}</td>
                <td>{{ $edu['concentration'] ?? '' }}</td>
                <td>{{ $edu['institution'] ?? '' }}</td>
                @if ($cvView->showEducationBoardColumn())
                    <td>{{ $edu['board'] ?? '' }}</td>
                @endif
                <td>{{ \ME\Portfolio\Support\Cv\Presenter::result($edu) }}</td>
                <td>{{ $edu['passing_year'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
