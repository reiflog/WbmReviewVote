import ReviewVotePlugin from "./review-vote/review-vote.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('ReviewVotePlugin', ReviewVotePlugin);
