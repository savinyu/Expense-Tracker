<?php

return [

    // App
    'app_name'              => 'Expense Tracker',

    // Profile page
    'profile_settings'           => 'Profile Settings',
    'profile_settings_desc'      => 'Manage your account preferences and default currency',
    'profile_information'        => 'Profile Information',
    'profile_information_desc'   => "Update your account's profile information and email address.",
    'name'                       => 'Name',
    'email'                      => 'Email',
    'email_unverified'           => 'Your email address is unverified.',
    'resend_verification'        => 'Click here to re-send the verification email.',
    'verification_link_sent'     => 'A new verification link has been sent to your email address.',
    'save'                       => 'Save',
    'saved'                      => 'Saved.',
    'update_password'            => 'Update Password',
    'update_password_desc'       => 'Ensure your account is using a long, random password to stay secure.',
    'current_password'           => 'Current Password',
    'new_password'               => 'New Password',
    'confirm_password'           => 'Confirm Password',
    'delete_account'             => 'Delete Account',
    'delete_account_desc'        => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
    'delete_account_confirm'     => 'Are you sure you want to delete your account?',
    'delete_account_confirm_desc'=> 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
    'password'                   => 'Password',

    // Roommate
    'roommate_connection'        => 'Roommate Connection',
    'roommate_connection_desc'   => 'Link with one roommate to share expenses. Both of you must agree to share each expense.',
    'roommate_invite_label'      => "Roommate's Email",
    'roommate_invite_hint'       => 'Enter the email of an existing user.',
    'roommate_link'              => 'Link Roommate',
    'roommate_linked_with'       => 'Linked with',
    'roommate_unlink'            => 'Unlink Roommate',
    'roommate_unlink_confirm'    => 'Are you sure? Your shared expenses will be returned to personal.',
    'roommate_linked_success'    => 'Roommate linked successfully.',
    'roommate_unlinked_success'  => 'Roommate unlinked. Shared expenses reverted to personal.',
    'roommate_not_found'         => 'No user found with that email.',
    'roommate_self'              => 'You cannot link yourself as a roommate.',
    'roommate_already_linked'    => 'You already have a roommate linked. Unlink first to switch.',
    'roommate_other_taken'       => 'That user is already linked with someone else.',

    // Shared expenses
    'share_with_roommate'        => 'Share with Roommate',
    'share_with_roommate_hint'   => 'Your roommate will review and accept before it counts as shared.',
    'pending_shared_alert'       => '{1} :name added :count shared expense awaiting your review.|[2,*] :name added :count shared expenses awaiting your review.',
    'review_pending'             => 'Review',
    'accept_shared'              => 'Accept',
    'shared_accepted'            => 'Shared expense accepted.',
    'shared_badge'               => 'Shared',
    'pending_badge'              => 'Pending review',

    // View filter
    'filter_view'                => 'View',
    'filter_view_all'            => 'All',
    'filter_view_personal'       => 'Personal Only',
    'filter_view_shared'         => 'Shared Only',

    // Navigation
    'profile'               => 'Profile',
    'download_csv'          => 'Download Data (CSV)',
    'log_out'               => 'Log Out',
    'switch_dark'           => 'Switch to dark mode',
    'switch_light'          => 'Switch to light mode',

    // Dashboard header
    'dashboard_title'       => 'Expense Dashboard',

    // Summary cards
    'total_spent_this_month' => 'Total Spent',
    'top_category'           => 'Top Category',
    'top_category_none'      => '—',
    'transaction_count'      => 'Transactions',

    // Chart
    'spending_by_category'       => 'Spending by Category',
    'no_expenses_this_month'     => 'No expenses recorded for this month yet.',

    // Currency
    'default_currency'      => 'Default Currency',
    'currency'              => 'Currency',
    'currency_hint'         => 'Override for this transaction',

    // Add expense form
    'add_new_expense'       => 'Add New Expense',
    'amount'                => 'Amount',
    'amount_hint'           => '',
    'category'              => 'Category',
    'select_category'       => 'Select a category…',
    'date'                  => 'Date',
    'description'           => 'Description',
    'description_hint'      => '(optional)',
    'description_placeholder' => 'What was this for?',
    'add_expense'           => 'Add Expense',
    'cancel'                => 'Cancel',

    // Receipt scan
    'receipt_scan'          => 'Scan a Receipt',
    'choose_receipt'        => 'Choose image…',
    'scan_receipt'          => 'Scan Receipt',
    'scanning'              => 'Scanning…',
    'scan_success_prefix'   => 'Fields populated:',
    'scan_no_data'          => 'Receipt scanned but no data could be extracted.',
    'scan_error_generic'    => 'Receipt scan failed. Please try again.',
    'scan_error_network'    => 'Network error. Please check your connection and try again.',

    // Filters
    'filter_expenses'       => 'Filter Expenses',
    'filter_all_categories' => 'All Categories',
    'filter_all_currencies' => 'All Currencies',
    'filter_from'           => 'From',
    'filter_to'             => 'To',
    'filter_apply'          => 'Apply',
    'filter_clear'          => 'Clear Filters',
    'active_filters'        => '{1} :count active filter|[2,*] :count active filters',
    'filter_active_label'   => 'Filtered results',
    'total_filtered'        => 'Filtered Total',

    // Expense list
    'all_expenses'          => 'All Expenses',
    'no_expenses_yet'       => 'No expenses recorded yet.',
    'entries'               => '{1} :count entry|[2,*] :count entries',
    'high_spend_threshold'      => 'High Spend Alert Threshold',
    'high_spend_threshold_hint' => 'Expenses at or above this amount will be highlighted in red. Leave blank to use the default for your currency.',
    'high_spend_badge'          => '≥:amount highlighted',
    'high_spend_note'           => 'Amounts at or above :amount are shown in red',

    // Table columns
    'col_date'              => 'Date',
    'col_category'          => 'Category',
    'col_description'       => 'Description',
    'col_amount'            => 'Amount',

    // Flash messages
    'expense_added'         => 'Expense added successfully.',

    // Date format used with PHP date()
    'month_year_format'     => 'F Y',

];
