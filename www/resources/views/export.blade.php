@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-default" style="margin-top: 110px">
				<div class="panel-heading">Export</div>

				<div class="panel-body">
                    <p>Choose a date range to export:</p>
                    <form action="{{ route('download') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group">
                        <div class="input-daterange input-group" data-provide="datepicker" data-endDate="0d" data-orientation="bottom left">
                            <input type="text" name="startDate" class="form-control" placeholder="Earliest date" value="{{ old('startDate', Carbon::now()->subDays(30)->format('d/m/Y')) }}" data-endDate="0d" data-orientation="bottom left" />
                            <span class="input-group-addon">to</span>
                            <input type="text" name="endDate" class="form-control" placeholder="Latest date" value="{{ old('endDate', Carbon::now()->format('d/m/Y')) }}" data-endDate="0d" data-orientation="bottom left"/>
                        </div>
                        </div>

                        <?php
                        $errorFormat = '<div class="alert alert-danger">:message</div>';
                        ?>
                        {!! $errors->first('startDate', $errorFormat) !!}
                        {!! $errors->first('endDate', $errorFormat) !!}

                        <button type="submit" class="btn btn-primary">Download</button>
                    </form>
				</div>
			</div>

		</div>
	</div>
</div>

@endsection

@section('body-close')
    <script>
        $.fn.datepicker.defaults.format = "dd/mm/yyyy";
        $.fn.datepicker.defaults.endDate = "0d";
        $.fn.datepicker.defaults.orientation = "bottom";
    </script>
@endsection
