@extends('app')

@section('content')
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">ET Building: Climate Comfort Monitoring</a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="/">SC5-214</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    @if (Auth::guest())
                        <li><a href="/auth/login">Login</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->name }} <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('export') }}">Export</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="/auth/logout">Logout</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
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
