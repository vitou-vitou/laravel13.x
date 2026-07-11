namespace AmberHarbor.Flows;

public static class PostFlow
{
  private const string RemoteVideo = "/sdcard/Download/farm-sample.mp4";

  public static int Run(
    FarmContext ctx,
    string? email = null,
    string? videoPath = null,
    string caption = "amber-harbor farm test #tiktok",
    bool skipLogin = false)
  {
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("post: adb_unavailable");
      return 1;
    }

    var acc = Core.AccountStore.Latest();
    email ??= acc?.Email;
    if (string.IsNullOrWhiteSpace(email))
    {
      Console.WriteLine("post: no_account");
      return 2;
    }

    videoPath ??= Core.FarmPaths.SampleVideo;
    if (!File.Exists(videoPath))
    {
      Console.WriteLine($"post: missing_video {videoPath}");
      return 2;
    }

    Console.WriteLine($"==> post {email} video={videoPath}");
    ctx.Adb.PushFile(videoPath, RemoteVideo);
    ctx.Ld.KillApp();
    Thread.Sleep(1000);
    ctx.Ld.RunApp();
    Thread.Sleep(8000);
    ctx.Ui.DismissPopups();

    if (!skipLogin && LoginFlow.Run(ctx, email, acc?.Password) != 0)
    {
      Console.WriteLine("post: login_required");
      return 1;
    }

    ctx.Ui.TapAnyOr(800, 850, "Create", "\\+");
    Thread.Sleep(3000);
    ctx.Ui.TapAny("Upload", "Gallery", "Videos");
    Thread.Sleep(2000);

    ctx.Ui.TapAnyOr(400, 500, "farm-sample", "Download", "Gallery");
    Thread.Sleep(2000);
    ctx.Ui.TapAnyOr(1400, 850, "Next", "Continue");
    Thread.Sleep(4000);
    ctx.Ui.TapAnyOr(1400, 850, "Next", "Continue");
    Thread.Sleep(3000);

    ctx.Ui.TapAnyOr(500, 200, "Describe", "caption", "Add description");
    Thread.Sleep(500);
    ctx.Adb.Text(caption);
    Thread.Sleep(1000);

    ctx.Ui.TapAnyOr(1200, 100, "Post", "Publish");
    Thread.Sleep(8000);

    for (var i = 0; i < 15; i++)
    {
      if (ctx.Ui.HasText("uploaded") || ctx.Ui.HasText("View profile") || ctx.Ui.HasText("Your video"))
      {
        Core.AccountStore.MarkPost(email);
        ctx.Ui.ScreenshotStep("post-success", ctx.Ld);
        Console.WriteLine($"post: success email={email}");
        return 0;
      }
      Thread.Sleep(3000);
    }

    ctx.Ui.ScreenshotStep("post-timeout", ctx.Ld);
    Console.WriteLine($"post: post_timeout email={email}");
    return 1;
  }
}
