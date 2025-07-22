@foreach ($permissions as $area => $permissionsArray)
  @if (count($permissionsArray) == 1)
    <?php $localPermission = $permissionsArray[0]; ?>
    <tbody class="permissions-group">
    <tr class="header-row permissions-row">
      <td class="col-md-5 tooltip-base permissions-item"
        data-tooltip="true"
        data-placement="right"
        title="{{ $localPermission['note'] }}"
      >
        @unless (empty($localPermission['label']))
         <h2>{{ $area . ': ' . $localPermission['label'] }}</h2>
        @else
          <h2>{{ $area }}</h2>
        @endunless
      </td>

      <td class="col-md-1 permissions-item">
        <label class="sr-only" for="{{ 'permission['.$localPermission['permission'].']' }}">{{ 'permission['.$localPermission['permission'].']' }}</label>
        @if (($localPermission['permission'] == 'superuser') && (!Auth::user()->isSuperUser()))
          <input
              disabled="disabled"
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '1')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="1"
          />
        @elseif (($localPermission['permission'] == 'admin') && (!Auth::user()->hasAccess('admin')))
          <input
              disabled="disabled"
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '1')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="1"
          />
        @else
          <input
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '1')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="1"
          />
        @endif

        
      </td>
      <td class="col-md-1 permissions-item">
        <label class="sr-only" for="{{ 'permission['.$localPermission['permission'].']' }}">{{ 'permission['.$localPermission['permission'].']' }}</label>
        @if (($localPermission['permission'] == 'superuser') && (!Auth::user()->isSuperUser()))
          <input
              disabled="disabled"
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '-1')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="-1"
          />
        @elseif (($localPermission['permission'] == 'admin') && (!Auth::user()->hasAccess('admin')))
          <input
              disabled="disabled"
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '-1')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="-1"
          />
        @else
          <input
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '-1')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="-1"
          />
        @endif
      </td>
      <td class="col-md-1 permissions-item">
        <label class="sr-only" for="{{ 'permission['.$localPermission['permission'].']' }}">
           {{ 'permission['.$localPermission['permission'].']' }}</label>
        @if (($localPermission['permission'] == 'superuser') && (!Auth::user()->isSuperUser()))
          <input
              disabled="disabled"
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '0')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="0"
          />
        @elseif (($localPermission['permission'] == 'admin') && (!Auth::user()->hasAccess('admin')))
          <input
              disabled="disabled"
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '0')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="0"
          />
        @else
          <input
              aria-label="permission[{{ $localPermission['permission'] }}]"
              @checked($userPermissions[$localPermission['permission']] == '0')
              name="permission[{{ $localPermission['permission'] }}]"
              type="radio"
              value="0"
          />
        @endif
      </td>
    </tr>
  </tbody>

  @else <!-- count($permissionsArray) == 1-->
  <tbody class="permissions-group">
    <tr class="header-row permissions-row">
      <td class="col-md-5 header-name">
        <h2> {{ $area }}</h2>
      </td>
      <td class="col-md-1 permissions-item">
        <label for="{{ $area }}" class="sr-only">{{ $area }}</label>
        <input
            value="1"
            data-checker-group="{{ str_slug($area) }}"
            aria-label="{{ $area }}"
            name="{{ $area }}"
            type="radio"
        />
      </td>
      <td class="col-md-1 permissions-item">
        <label for="{{ $area }}" class="sr-only">{{ $area }}</label>
        <input
            value="-1"
            data-checker-group="{{ str_slug($area) }}"
            aria-label="{{ $area }}"
            name="{{ $area }}"
            type="radio"
        />
      </td>
      <td class="col-md-1 permissions-item">
        <label for="{{ $area }}" class="sr-only">{{ $area }}</label>
        <input
            value="0"
            data-checker-group="{{ str_slug($area) }}"
            aria-label="{{ $area }}"
            name="{{ $area }}"
            type="radio"
        />
      </td>
    </tr>

    @foreach ($permissionsArray as $index => $permission)
      <tr class="permissions-row">
        @if ($permission['display'])
          <td
            class="col-md-5 tooltip-base permissions-item"
            data-tooltip="true"
            data-placement="right"
            title="{{ $permission['note'] }}"
          >
            {{ $permission['label'] }}
          </td>
          <td class="col-md-1 permissions-item">
            <label class="sr-only" for="{{ 'permission['.$permission['permission'].']' }}">{{ 'permission['.$permission['permission'].']' }}</label>
            <input
                value="1"
                class="radiochecker-{{ str_slug($area) }}"
                aria-label="permission[{{ $permission['permission'] }}]"
                @checked($userPermissions[$permission['permission']] == '1')
                @disabled(($permission['permission'] == 'superuser') && (!Auth::user()->isSuperUser()))
                name="permission[{{ $permission['permission'] }}]"
                type="radio"
            />
          </td>
          <td class="col-md-1 permissions-item">
            <input
                value="-1"
                class="radiochecker-{{ str_slug($area) }}"
                aria-label="permission[{{ $permission['permission'] }}]"
                @checked($userPermissions[$permission['permission']] == '-1')
                @disabled(($permission['permission'] == 'superuser') && (!Auth::user()->isSuperUser()))
                name="permission[{{ $permission['permission'] }}]"
                type="radio"
            />
          </td>
          <td class="col-md-1 permissions-item">
            <input
                value="0"
                class="radiochecker-{{ str_slug($area) }}"
                aria-label="permission[{{ $permission['permission'] }}]"
                @checked($userPermissions[$permission['permission']] =='0')
                @disabled(($permission['permission'] == 'superuser') && (!Auth::user()->isSuperUser()))
                name="permission[{{ $permission['permission'] }}]"
                type="radio"
            />
          </td>
        @endif
      </tr>
    @endforeach
    </tbody>
  @endif
@endforeach
