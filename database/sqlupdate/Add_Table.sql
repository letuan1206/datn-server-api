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