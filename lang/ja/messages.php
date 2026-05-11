<?php

return [

    // App
    'app_name'              => '支出トラッカー',

    // Profile page
    'profile_settings'           => 'プロフィール設定',
    'profile_settings_desc'      => 'アカウントの設定とデフォルト通貨を管理します',
    'profile_information'        => 'プロフィール情報',
    'profile_information_desc'   => 'アカウントのプロフィール情報とメールアドレスを更新してください。',
    'name'                       => '氏名',
    'email'                      => 'メールアドレス',
    'email_unverified'           => 'メールアドレスが確認されていません。',
    'resend_verification'        => '確認メールを再送するにはこちらをクリックしてください。',
    'verification_link_sent'     => '新しい確認リンクをメールアドレスに送信しました。',
    'save'                       => '保存する',
    'saved'                      => '保存しました。',
    'update_password'            => 'パスワードの変更',
    'update_password_desc'       => 'セキュリティのため、長くランダムなパスワードをご利用ください。',
    'current_password'           => '現在のパスワード',
    'new_password'               => '新しいパスワード',
    'confirm_password'           => 'パスワードの確認',
    'delete_account'             => 'アカウントを削除',
    'delete_account_desc'        => 'アカウントを削除すると、すべてのリソースとデータが完全に削除されます。削除前に必要なデータをダウンロードしてください。',
    'delete_account_confirm'     => '本当にアカウントを削除しますか？',
    'delete_account_confirm_desc'=> 'アカウントを削除すると、すべてのデータが完全に削除されます。削除を確認するためにパスワードを入力してください。',
    'password'                   => 'パスワード',

    // Navigation
    'profile'               => 'プロフィール',
    'download_csv'          => 'データをダウンロード (CSV)',
    'log_out'               => 'ログアウト',
    'switch_dark'           => 'ダークモードに切り替え',
    'switch_light'          => 'ライトモードに切り替え',

    // Dashboard header
    'dashboard_title'       => '支出ダッシュボード',

    // Summary cards
    'total_spent_this_month' => '合計支出',
    'top_category'           => '最多カテゴリー',
    'top_category_none'      => '—',
    'transaction_count'      => '取引件数',

    // Chart
    'spending_by_category'       => 'カテゴリー別支出',
    'no_expenses_this_month'     => '今月の支出はまだ記録されていません。',

    // Currency
    'default_currency'      => 'デフォルト通貨',
    'currency'              => '通貨',
    'currency_hint'         => 'この取引の通貨を変更',

    // Add expense form
    'add_new_expense'       => '支出を追加',
    'amount'                => '金額',
    'amount_hint'           => '',
    'category'              => 'カテゴリー',
    'select_category'       => 'カテゴリーを選択…',
    'date'                  => '日付',
    'description'           => '説明',
    'description_hint'      => '（任意）',
    'description_placeholder' => '何の支出ですか？',
    'add_expense'           => '追加する',
    'cancel'                => 'キャンセル',

    // Receipt scan
    'receipt_scan'          => 'レシートをスキャン',
    'choose_receipt'        => '画像を選択…',
    'scan_receipt'          => 'スキャン',
    'scanning'              => 'スキャン中…',
    'scan_success_prefix'   => '入力済み項目:',
    'scan_no_data'          => 'スキャンしましたが、データを抽出できませんでした。',
    'scan_error_generic'    => 'スキャンに失敗しました。もう一度お試しください。',
    'scan_error_network'    => 'ネットワークエラーが発生しました。接続を確認してください。',

    // Filters
    'filter_expenses'       => '支出を絞り込む',
    'filter_all_categories' => 'すべてのカテゴリー',
    'filter_all_currencies' => 'すべての通貨',
    'filter_from'           => '開始日',
    'filter_to'             => '終了日',
    'filter_apply'          => '絞り込む',
    'filter_clear'          => '条件をクリア',
    'active_filters'        => ':count 件の絞り込み中',
    'filter_active_label'   => '絞り込み結果',
    'total_filtered'        => '絞り込み合計',

    // Expense list
    'all_expenses'          => 'すべての支出',
    'no_expenses_yet'       => '支出はまだ記録されていません。',
    'entries'               => ':count 件',
    'high_spend_threshold'      => '高額支出アラートのしきい値',
    'high_spend_threshold_hint' => 'この金額以上の支出は赤で強調表示されます。空白のままにすると、通貨ごとのデフォルト値が使用されます。',
    'high_spend_badge'          => '≥:amount を強調',
    'high_spend_note'           => ':amount 以上の支出は赤で表示されます',

    // Table columns
    'col_date'              => '日付',
    'col_category'          => 'カテゴリー',
    'col_description'       => '説明',
    'col_amount'            => '金額',

    // Flash messages
    'expense_added'         => '支出を追加しました。',

    // Date format used with PHP date()
    'month_year_format'     => 'Y年n月',

];
