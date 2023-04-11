
# DELETE FROM `cmf_users` WHERE user_type != 1 OR (user_type = 1 and admin_type = 1);

DELETE FROM cmf_users WHERE user_type = 2;
DELETE FROM cmf_users WHERE user_type = 3;
DELETE FROM cmf_users WHERE user_type = 4;
DELETE FROM cmf_users WHERE user_type = 5;
DELETE FROM cmf_users WHERE user_type = 6;
DELETE FROM cmf_users WHERE user_type = 7;
DELETE FROM cmf_users WHERE user_type = 8;

TRUNCATE `cmf_activity_config`;
TRUNCATE `cmf_activity_reward_log`;
TRUNCATE `cmf_admin_log`;
TRUNCATE `cmf_appointment_order`;
TRUNCATE `cmf_log_complex`;
TRUNCATE `cmf_asset`;
TRUNCATE `cmf_atmosphere_live`;
TRUNCATE `cmf_atmosphere_reward`;
TRUNCATE `cmf_agent_reward`;

TRUNCATE `cmf_bankcard_share`;
TRUNCATE `cmf_bank_card`;
TRUNCATE `cmf_bar`;
TRUNCATE `cmf_bar_comment`;
TRUNCATE `cmf_bet_config`;

TRUNCATE `cmf_car`;
TRUNCATE `cmf_clearheat_log`;
TRUNCATE `cmf_comments`;
TRUNCATE `cmf_complex_summary`;
TRUNCATE `cmf_commission_set`;
TRUNCATE `cmf_common_action_log`;
TRUNCATE `cmf_consumption_collect`;

TRUNCATE `cmf_family`;
TRUNCATE `cmf_family_auth`;
TRUNCATE `cmf_family_profit`;
TRUNCATE `cmf_feedback`;

TRUNCATE `cmf_game`;
TRUNCATE `cmf_getcode_limit_ip`;
TRUNCATE `cmf_guard_users`;

TRUNCATE `cmf_impression_label`;
TRUNCATE `cmf_integral_log`;

TRUNCATE `cmf_jmessagerecord`;

TRUNCATE `cmf_keeper`;
TRUNCATE `cmf_kvconfig`;

TRUNCATE `cmf_links`;
TRUNCATE `cmf_liveing_log`;
TRUNCATE `cmf_liveing_set`;
TRUNCATE `cmf_loginbonus`;
TRUNCATE `cmf_log_api`;
TRUNCATE `cmf_log_socket`;
TRUNCATE `cmf_lottery_config`;
TRUNCATE `cmf_lottery_config_copy`;
TRUNCATE `cmf_users_lottery`;

TRUNCATE `cmf_offlinepay`;

TRUNCATE `cmf_posts`;
TRUNCATE `cmf_profit_daysharing`;
TRUNCATE `cmf_profit_sharing`;
TRUNCATE `cmf_pushrecord`;

TRUNCATE `cmf_recommend`;
TRUNCATE `cmf_red`;
TRUNCATE `cmf_red_record`;
TRUNCATE `cmf_red_record_detail`;

TRUNCATE `cmf_sendcode`;
TRUNCATE `cmf_shop_manager`;
TRUNCATE `cmf_shop_order_purchase`;
TRUNCATE `cmf_shopping_voucher`;
TRUNCATE `cmf_station_user`;
TRUNCATE `cmf_user_transfer_yuebao`;
TRUNCATE `cmf_virtualname`;

TRUNCATE `cmf_task`;
TRUNCATE `cmf_task_classification`;
TRUNCATE `cmf_task_loginreward`;
TRUNCATE `cmf_task_plan`;
TRUNCATE `cmf_task_rewardlog`;
TRUNCATE `cmf_tenant_profit`;
TRUNCATE `cmf_terms`;
TRUNCATE `cmf_term_relationships`;
TRUNCATE `cmf_test_log`;

TRUNCATE `cmf_users_action`;
TRUNCATE `cmf_users_agent`;
TRUNCATE `cmf_users_agent_code`;
TRUNCATE `cmf_users_agent_profit`;
TRUNCATE `cmf_users_agent_profit_recode`;
TRUNCATE `cmf_users_attention`;
TRUNCATE `cmf_users_auth`;
TRUNCATE `cmf_users_basicsalary`;
TRUNCATE `cmf_users_black`;
TRUNCATE `cmf_users_car`;
TRUNCATE `cmf_users_cashrecord`;
TRUNCATE `cmf_users_cash_account`;
TRUNCATE `cmf_users_charge`;
TRUNCATE `cmf_users_charge_admin`;
TRUNCATE `cmf_users_chatroom`;
TRUNCATE `cmf_users_chatroom_friends`;
TRUNCATE `cmf_users_coinrecord`;
TRUNCATE `cmf_users_family`;
TRUNCATE `cmf_users_gamerecord`;
TRUNCATE `cmf_users_label`;
TRUNCATE `cmf_users_live`;
TRUNCATE `cmf_users_livemanager`;
TRUNCATE `cmf_users_liverecord`;
TRUNCATE `cmf_users_music`;
TRUNCATE `cmf_users_music_collection`;
TRUNCATE `cmf_users_noble`;
TRUNCATE `cmf_users_noble_log`;
TRUNCATE `cmf_users_pushid`;
TRUNCATE `cmf_users_report`;
TRUNCATE `cmf_users_share`;
TRUNCATE `cmf_users_sharedetail`;
TRUNCATE `cmf_users_share_log`;
TRUNCATE `cmf_users_sign`;
TRUNCATE `cmf_users_super`;
TRUNCATE `cmf_users_video`;
TRUNCATE `cmf_users_video_black`;
TRUNCATE `cmf_users_video_collection`;
TRUNCATE `cmf_users_video_comments`;
TRUNCATE `cmf_users_video_comments_like`;
TRUNCATE `cmf_users_video_like`;
TRUNCATE `cmf_users_video_report`;
TRUNCATE `cmf_users_video_step`;
TRUNCATE `cmf_users_video_view`;
TRUNCATE `cmf_users_vip`;
TRUNCATE `cmf_users_voterecord`;
TRUNCATE `cmf_users_zombie`;
TRUNCATE `cmf_user_authinfo`;
TRUNCATE `cmf_user_keyword`;
TRUNCATE `cmf_user_keywordset`;
TRUNCATE `cmf_users_stoplog`;
TRUNCATE `cmf_user_task`;
TRUNCATE `cmf_yh_user_task`;
TRUNCATE `cmf_users_video_buy`;

TRUNCATE `cmf_video`;
TRUNCATE `cmf_video_active_member`;
TRUNCATE `cmf_video_comments`;
TRUNCATE `cmf_video_download`;
TRUNCATE `cmf_video_long`;
TRUNCATE `cmf_video_profit`; 
TRUNCATE `cmf_video_uplode_reward`;
TRUNCATE `cmf_video_watch_record`;

TRUNCATE  `cmf_yuebao_rate`;

TRUNCATE `cmf_welfare_exchange_log`;
TRUNCATE `cmf_work_order`;
