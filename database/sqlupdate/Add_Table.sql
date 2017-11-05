-- Create Table Log_Login
CREATE TABLE [dbo].[Log_Login](
	[account] [varchar](10) NULL,
	[ip] [nvarchar](50) NULL,
	[time] [int] NULL,
	[description] [nvarchar](max) NULL
) ON [PRIMARY]

-- Create Table BK_SMS_Service
CREATE TABLE [dbo].[BK_SMS_Service](
	[account] [varchar](10) NOT NULL,
	[phone_number] [varchar](20) NOT NULL,
	[info_change] [varchar](50) NOT NULL,
	[sms_code] [varchar](10) NOT NULL,
	[sms_type] [tinyint] NOT NULL,
	[time] [int] NOT NULL,
	[status] [tinyint] NOT NULL
) ON [PRIMARY]

-- Create Table Log_BankTransfer
CREATE TABLE [dbo].[Log_BankTransfer](
	[from_account] [varchar](10) NOT NULL,
	[to_account] [varchar](10) NOT NULL,
	[quality] [int] NOT NULL,
	[description] [nvarchar](255) NULL,
	[time] [int] NULL,
	[type] [nvarchar](50) NOT NULL
) ON [PRIMARY]

-- Create Table Log_Item_Sliver_Change
CREATE TABLE [dbo].[Log_Item_Sliver_Change](
	[account] [nvarchar](10) NOT NULL,
	[name] [nvarchar](10) NOT NULL,
	[item_type] [varchar](50) NOT NULL,
	[item_seri] [nvarchar](10) NOT NULL,
	[item_value] [int] NOT NULL,
	[time] [int] NOT NULL
) ON [PRIMARY]

-- Create Table BK_Event_CheckIn
CREATE TABLE [dbo].[BK_Event_CheckIn](
	[account] [varchar](10) NOT NULL,
	[time] [datetime] NOT NULL,
	[day_check] [int] NOT NULL,
	[location] [nvarchar](255) NULL,
	[description] [nvarchar](max) NULL
) ON [PRIMARY]

ALTER TABLE [dbo].[BK_Event_CheckIn] ADD  CONSTRAINT [DF_BK_Event_CheckIn_day_check]  DEFAULT ((0)) FOR [day_check]
GO

CREATE TABLE [dbo].[BK_Super_Market](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[account] [varchar](10) NOT NULL,
	[name] [varchar](10) NULL,
	[code] [varchar](10) NULL,
	[item_code] [nvarchar](50) NOT NULL,
	[item_type] [tinyint] NOT NULL,
	[item_price] [int] NOT NULL,
	[time_up] [datetime] NOT NULL,
	[dw] [tinyint] NOT NULL,
	[dk] [tinyint] NOT NULL,
	[elf] [tinyint] NOT NULL,
	[mg] [tinyint] NOT NULL,
	[dl] [tinyint] NOT NULL,
	[sum] [tinyint] NOT NULL,
	[rf] [tinyint] NOT NULL,
	[status] [tinyint] NOT NULL,
	[account_buy] [varchar](10) NULL,
	CONSTRAINT [PK_BK_Super_Market] PRIMARY KEY CLUSTERED
		(
			[id] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

-- Table Check Action
CREATE TABLE [dbo].[BK_Check_Action](
	[action_name] [nvarchar](50) NULL,
	[action_time] [date] NULL,
	[status] [tinyint] NOT NULL
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[BK_Check_Action] ADD  CONSTRAINT [DF_BK_Check_Action_status]  DEFAULT ((0)) FOR [status]
GO

CREATE TABLE [dbo].[BK_Config_Reset](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[reset] [int] NOT NULL,
	[point] [int] NOT NULL,
	[sliver] [int] NOT NULL,
	[level_reset] [int] NOT NULL,
	[chaos_reset] [int] NOT NULL,
	[cre_reset] [int] NOT NULL,
	[blue_reset] [int] NOT NULL,
	[zen_reset] [int] NOT NULL,
	[leadership] [int] NOT NULL,
	[point_online] [int] NOT NULL,
	[level_reset_vip] [int] NOT NULL,
	CONSTRAINT [PK_BK_Config_Reset] PRIMARY KEY CLUSTERED
		(
			[id] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[BK_Config_Reset] ADD  CONSTRAINT [DF_BK_Config_Reset_leadership]  DEFAULT ((0)) FOR [leadership]
GO

ALTER TABLE [dbo].[BK_Config_Reset] ADD  CONSTRAINT [DF_BK_Config_Reset_point_online]  DEFAULT ((0)) FOR [point_online]
GO

ALTER TABLE [dbo].[BK_Config_Reset] ADD  CONSTRAINT [DF_BK_Config_Reset_level_reset_vip]  DEFAULT ((20)) FOR [level_reset_vip]
GO

CREATE TABLE [dbo].[BK_Config_Relife](
	[relife] [int] NOT NULL,
	[reset] [int] NOT NULL
) ON [PRIMARY]

GO

CREATE TABLE [dbo].[BK_Config_Limit_Reset](
	[reset_top] [int] NOT NULL,
	[distance_top_day_reset] [int] NOT NULL,
	[max_reset_in_day] [int] NOT NULL,
	[percent_saturday] [int] NOT NULL,
	[percent_sunday] [int] NOT NULL
) ON [PRIMARY]

GO

CREATE TABLE [dbo].[BK_Top_Resets](
	[account] [varchar](10) NOT NULL,
	[name] [varchar](10) NOT NULL,
	[date_reset] [datetime] NOT NULL,
	[reset_type] [int] NOT NULL
) ON [PRIMARY]

GO

CREATE TABLE [dbo].[BK_Web_Shops](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[code] [varchar](10) NULL,
	[item_code] [nvarchar](50) NOT NULL,
	[item_type] [tinyint] NOT NULL,
	[item_price] [int] NOT NULL,
	[time_up] [datetime] NOT NULL,
	[dw] [tinyint] NOT NULL,
	[dk] [tinyint] NOT NULL,
	[elf] [tinyint] NOT NULL,
	[mg] [tinyint] NOT NULL,
	[dl] [tinyint] NOT NULL,
	[sum] [tinyint] NOT NULL,
	[rf] [tinyint] NOT NULL,
	[status] [tinyint] NOT NULL,
	CONSTRAINT [PK_BK_Web_Shops] PRIMARY KEY CLUSTERED
		(
			[id] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

CREATE TABLE [dbo].[Log_Resets](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[account] [varchar](10) NOT NULL,
	[name] [varchar](10) NOT NULL,
	[reset_type] [tinyint] NOT NULL,
	[reset_time] [datetime] NOT NULL,
	CONSTRAINT [PK_Log_Resets] PRIMARY KEY CLUSTERED
		(
			[id] ASC
		)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]
