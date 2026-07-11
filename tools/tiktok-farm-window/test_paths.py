from paths import CREATOR_ROOT, TOOL_ROOT


def test_paths_exist():
    assert TOOL_ROOT.name == "tiktok-farm-window"
    assert CREATOR_ROOT.name == "tiktok-account-creator"
