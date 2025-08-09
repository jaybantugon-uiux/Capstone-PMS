@extends('app')

@section('content')
    <div class="container">
        <h1>Task Calendar</h1>

        <!-- Navigation -->
        <div class="mb-3">
            <a href="?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}" class="btn btn-secondary"><</a>
            <span>{{ $date->format('F Y') }}</span>
            <a href="?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}" class="btn btn-secondary">></a>
        </div>

        <!-- Calendar Grid -->
        <table class="calendar">
            <thead>
                <tr>
                    <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                </tr>
            </thead>
            <tbody> 
                @php
                    $dayCounter = 1;
                    $totalDays = $date->daysInMonth;
                @endphp
                @for($week = 0; $week < 6; $week++)
                    <tr>
                        @for($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++)
                            @if($week == 0 && $dayOfWeek < $firstDayOfWeek)
                                <td></td>
                            @elseif($dayCounter > $totalDays)
                                <td></td>
                            @else
                                <td @if($dayCounter == $today->day && $month == $today->month && $year == $today->year) class="today" @endif>
                                    <div class="day-number">{{ $dayCounter }}</div>
                                    @if(isset($calendar[$dayCounter]))
                                        <ul>
                                            @foreach($calendar[$dayCounter] as $task)
                                                <li><a href="{{ route('tasks.show', $task) }}">{{ $task->task_name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                @php $dayCounter++; @endphp
                            @endif
                        @endfor
                    </tr>
                    @if($dayCounter > $totalDays)
                        @break
                    @endif
                @endfor
            </tbody>
        </table>
    </div>

    <!-- Inline CSS for simplicity -->
    <style>
        .calendar {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar th, .calendar td {
            border: 1px solid #ddd;
            padding: 5px;
            vertical-align: top;
            height: 100px;
        }
        .calendar .day-number {
            font-weight: bold;
        }
        .calendar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .calendar li {
            margin-bottom: 5px;
        }
        .today {
            background-color: #e6f7ff;
        }
    </style>
@endsection