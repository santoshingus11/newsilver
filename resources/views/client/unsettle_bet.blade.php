@extends('web_layout.app')
@section('style')

@endsection
@section('content')

<div class="col-md-10 pxxs-0"><router-outlet></router-outlet><app-unsettled-bet>
        <div class="wrapper-inner user_screen">
            <h2 class="user-title">My Bet</h2>
            <div class="border border-light">
                <div class="filter m-2">
                    <div class="form-row align-items-center">
                        <div class="col-md-2 col-6"><input type="text" name="from" placeholder="Datepicker" bsdatepicker="" class="form-control ng-untouched ng-pristine ng-valid"><!----></div>
                        <div class="col-md-2 col-6"><input type="text" name="to" placeholder="Datepicker" bsdatepicker="" class="form-control ng-untouched ng-pristine ng-valid"><!----></div>
                        <div class="col-md-1 col-12"><button type="button" class="btn btn-primary btn-block">submit</button></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="btDataTable" class="table table-bordered btDataTable table-striped table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Event Name</th>
                                <th class="text-center">Stake</th>
                                <th class="text-center">Profit</th>
                                <th class="text-center">Match date</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($sorted_merged_played_matches_array as $b)
                            <tr>
                                <td class="text-center">{{$b->id}}</td>
                                <td class="text-center">{{$b->team_name}}</td>
                                <td class="text-center">{{$b->bet_stake}}</td>
                                <td class="text-center">{{$b->bet_profit}}</td>
                                <td class="text-center">{{$b->created_at}}</td>
                            </tr><!---->
                            @endforeach
                        </tbody>
                        <tbody>
                            
                            <td colspan="11" class="noData"> No Data Found</td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </app-unsettled-bet>
</div>
@endsection
@section('script')
<script>
    /* Script Goes Here */

    /* Script Goes Here */
</script>
@endsection