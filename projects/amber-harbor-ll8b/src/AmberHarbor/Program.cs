namespace AmberHarbor;

static class Program
{
  [STAThread]
  static int Main(string[] args)
  {
    if (args.Length > 0 && args[0] is not ("gui" or "--gui"))
    {
      if (args[0] is "-h" or "--help" or "help")
        return CliRunner.PrintHelp();
      return CliRunner.Run(args);
    }

    ApplicationConfiguration.Initialize();
    Application.Run(new MainForm());
    return 0;
  }
}
