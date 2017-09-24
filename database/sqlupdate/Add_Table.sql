-- Create Table Log_Login
CREATE TABLE [dbo].[Log_Login](
	[account] [varchar](10) NULL,
	[ip] [nvarchar](50) NULL,
	[time] [int] NULL,
	[description] [nvarchar](max) NULL
) ON [PRIMARY]
