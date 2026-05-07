<div>
    @if($tokens->isEmpty())
        <p class="text-muted">{{ trans('admin/settings/general.oauth_personal_access_tokens_none') }}</p>
    @else
        <div id="PersonalAccessTokensToolbar" class="pull-left" style="min-width: 280px; padding-top: 10px;"></div>
        <div class="table-responsive">
            <table
                class="table table-striped snipe-table"
                data-toolbar="#PersonalAccessTokensToolbar"
                data-toggle="table"
                data-sort-name="created_at"
                data-sort-order="desc"
            >
                <thead>
                    <tr>
                        <th data-field="name" data-sortable="true">{{ trans('general.name') }}</th>
                        <th data-field="user" data-sortable="true">{{ trans('general.created_by') }}</th>
                        <th data-field="client" data-sortable="true">{{ trans('admin/settings/general.oauth_client') }}</th>
                        <th data-field="status" data-sortable="true">{{ trans('general.status') }}</th>
                        <th data-field="created_at" data-sortable="true">{{ trans('general.created_at') }}</th>
                        <th data-field="expires_at" data-sortable="true">{{ trans('general.expires') }}</th>
                        <th data-field="actions" data-sortable="false">
                            <span class="sr-only">{{ trans('general.actions') }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tokens as $token)
                        @php
                            $isRevoked         = (int) $token->revoked === 1;
                            $isExpired         = !$isRevoked && $token->expires_at && \Carbon\Carbon::parse($token->expires_at)->isPast();
                            $profileLabel      = $token->display_name ?: $token->username;
                            $isSoftDeletedUser = $token->user_deleted_at !== null;
                        @endphp
                        <tr>
                            <td>{{ $token->name ?: $token->id }}</td>
                            <td>
                                @if($token->existing_user_id)
                                    <a href="{{ route('users.show', $token->token_user_id) }}">
                                        @if($isSoftDeletedUser)
                                            <del>{{ $profileLabel }}</del>
                                        @else
                                            {{ $profileLabel }}
                                        @endif
                                    </a>
                                @else
                                    {{ trans('admin/settings/general.oauth_deleted_user', ['id' => $token->token_user_id]) }}
                                @endif
                            </td>
                            <td>{{ $token->client_name }}</td>
                            <td>
                                @if($isRevoked)
                                    <span class="label label-danger">
                                         <x-icon type="x"/>
                                        {{ trans('admin/settings/general.oauth_token_status_revoked') }}</span>
                                @elseif($isExpired)
                                    <span class="label label-warning">
                                         <x-icon type="warning"/>
                                        {{ trans('admin/settings/general.oauth_token_status_expired') }}</span>
                                @else
                                    <span class="label label-success">
                                         <x-icon type="checkmark"/>
                                        {{ trans('admin/settings/general.oauth_token_status_active') }}</span>
                                @endif
                            </td>
                            <td>{{ $token->created_at ? \App\Helpers\Helper::getFormattedDateObject($token->created_at, 'datetime', false) : '' }}</td>
                            <td>{{ $token->expires_at ? \App\Helpers\Helper::getFormattedDateObject($token->expires_at, 'datetime', false) : '' }}</td>
                            <td class="text-right">
                                @if($isRevoked)
                                    <form method="POST" action="{{ route('settings.oauth.tokens.unrevoke', ['token' => $token->id]) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-default text-success" data-tooltip="true" title="{{ trans('admin/settings/general.oauth_unrevoke') }}">
                                            <i class="fa-solid fa-toggle-off"></i>
                                        </button>
                                    </form>
                                @elseif(!$isExpired)
                                    <form method="POST" action="{{ route('settings.oauth.tokens.revoke', ['token' => $token->id]) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-default text-danger" data-tooltip="true" title="{{ trans('admin/settings/general.oauth_revoke') }}">
                                            <i class="fa-solid fa-toggle-on"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

