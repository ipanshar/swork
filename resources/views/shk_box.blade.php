<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta  http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
    @page{   
        margin-left: 3%;
        margin-right: 2%;
        margin-top: 5px;
        margin-bottom: 0px;
    }
    .page-break{
        page-break-after: always;
    }
    .barcode{
        width: 100%;
     
        text-align: center; 
    }

    .created_at{
width: 100%;
text-align: right;
font-size: 10px;
font-style: italic;
    }
    .articles{
        width: 100%;
        height: 50px;
        font-size: 12px;
    }
    .title{
        text-align: center;
        text-decoration: dashed;
        font-size: 15px;
    }
    *{ font-family: DejaVu Sans mono !important;}
</style>
    </head>
<div>
@foreach($data as $d)
<div class="title">{{'#'.$title->id.'-'.$title->owner.'-'.$title->name}}</div>
<div class="created_at">
    <div>{{$d->created_at}}</div>
    <div>{{$d->user_name}}</div>
</div>
<div class="articles">
@foreach($operations as $o)
 @if($d->id==$o->box_id)
{{html_entity_decode($o->name).'('.$o->size.')'.'-'.$o->num.'; '}}
 @endif
@endforeach
</div>
 <div class="barcode"><span>{!! DNS1D::getBarcodeHTML($d->box_name,'C128',3.1,75)  !!}</span>
    <b>{{$d->box_name}}</b>
</div>

@if(count($data)>$loop->iteration)
<div class="page-break"></div>
    @endif
    
@endforeach
</div>
</html>