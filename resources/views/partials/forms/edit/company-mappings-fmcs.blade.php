<tbody class="fmcs_mappings">
  @foreach ($companies as $id => $company)
    <tr class="fmcs_mappings-row">
      <td class="col-md-8 company-name"> {{ $company }}</td>
      <td class="col-md-1 company-checkbox">
        <input class="form-check-input" type="checkbox" id="checkboxNoLabel" value={{$id}} name=companyMappings[]
          @if($user->company_id == $id) disabled title="{{ trans('general.company_own_mapped') }}" @endif
          @if($companyMappings->contains($id)) checked @endif>
      </td>
    </tr>
  @endforeach
</tbody>
