<div class="form-group group">
    <select name="tags[]" class="form-control select-tabs" multiple="multiple">
        @if(have_posts($tags))
            @foreach ($tags as $tag)
                <option value="{{ $tag->name }}" selected="selected">{{ $tag->name }}</option>
            @endforeach
        @endif
    </select>
    <p style="color:#999;margin:5px 0 5px 0;">Dùng "," hoặc "tab" để tạo tag mới khi không có kết quả tìm kiếm</p>
</div>
<script defer>
	$(function () {
		$(".select-tabs").select2({
			tags: true,
			tokenSeparators: [',', '\n'],
			minimumInputLength: 1,
			ajax: {
				url: ajax+'?action=AdminTagAjax::search',
				dataType: "json",
				delay: 500,
				data: function(term, page) {
					return {
						q: term
					};
				},
				processResults: function (data, params) {
					return {
						results: $.map(data.results, function (item) {
							return {
								text: item.name,
								id: item.name,
								data: item
							};
						})
					};
				}
			}
		})
	});
</script>