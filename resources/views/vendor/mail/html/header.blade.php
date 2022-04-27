<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<!-- <img src="https://drive.google.com/file/d/1-jXxdchpGBUqczcWZzOf6ED6Com7_PJp/view?usp=sharing" alt="image"> -->
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
