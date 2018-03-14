// JavaScript Document
//初始化变量
//e = document.getElementById('end');
var pokes = {};
pokes.matchingGame = {};
pokes.matchingGame.cardWidth = 80;//牌宽
pokes.matchingGame.cardHeight = 120;//牌高

pokes.matchingGame.deck = [];

var cardName = [
   "cardAK", "cardAK",
   "cardAQ", "cardAQ",
   "cardAJ", "cardAJ",
   "cardBK", "cardBK",
   "cardBQ", "cardBQ",
   "cardBJ", "cardBJ",   
   "cardCK", "cardCK",
   "cardCQ", "cardCQ",
   "cardCJ", "cardCJ",
   "cardDK", "cardDK",
   "cardDQ", "cardDQ",
   "cardDJ", "cardDJ"
];
//随机产生6对卡牌
function randomCard()
{
	ranPokes = new Array();
	var p, i, j, ok = false;
	//ranPokes.push(Math.floor(Math.random()*24));
	for(i=0;i<6;i++){
	   ok = true;
	   p = Math.floor(Math.random()*24);
	   for(j=0;j<ranPokes.length;j++){//与已经存入的元素比较
		  if(p==ranPokes[j]){
			 i--; 
			 ok = false; break;
		  }   
	   }
	   if(ok){
		   ranPokes.push(p); //配对
		   (p%2==0)?ranPokes.push(p+1):ranPokes.push(p-1);
	   } 
    }
}
function prepare(){
	//var h;
	for(i=0;i<ranPokes.length;i++)//将随机的图片存入数组
	{ 
        pokes.matchingGame.deck[i] = cardName[ranPokes[i]];	   
	    //h+=ranPokes[i]+' '+cardName[ranPokes[i]];
	    //h+=' ';
    }
	//alert(h);
}
//随机排序扑克的函数，返回 -1 或 1.
function shuffle()
{  //alert('c');
   return Math.random()>0.5 ? -1:1	
}
var pairs = 0,clickNum = 0, score = 0;
//翻牌功能的实现
function selectCard(){//alert('se');
  clickNum++; 
  document.getElementById('step').innerHTML = clickNum;
  var $fcard = $(".card-flipped");
  //翻了两张牌后退出翻牌
  if($fcard.length > 1)
  {
	 return;  
  }	
  $(this).addClass("card-flipped");
  //若翻动了两张牌，检测一致性
  var $fcards = $(".card-flipped");
  if($fcards.length == 2)
  {
	  setTimeout(function(){
		 checkPattern($fcards);},700);  
  }
}
//检测2张牌是否相同
function checkPattern(cards)
{
   var pattern1 = $(cards[0]).data("pattern");
   var pattern2 = $(cards[1]).data("pattern");
   
   $(cards).removeClass("card-flipped");
   if(pattern1 == pattern2)
   {
	  $(cards).addClass("card-removed").bind("webkitTransitionEnd",
	  function(){
		  $(this).remove();
	  });    
      pairs++;	
	  score+=1000;
   }	
   if(pairs==6){
	   score-=totalTime/100*150;
	   document.getElementById('end').innerHTML += '<p>'+'点击次数：'+clickNum+'次'+'</p>'+'<p>'+'共花时：'+totalTime+'秒'+'</p>'+'<p>'+'得分：'+score+'</p>';
	   document.getElementById('time').innerHTML = totalTime;
	   clearTimeout(clock);//清除定时器
	   $("#end").css("display","block");
   }
}
var totalTime = 0, clock = 0;
function timeAdd(){
   	totalTime+=1;//累加时间
	//totalTime = totalTime.toFixed(2);
	document.getElementById('time').innerHTML = totalTime+' s';
	clock = setTimeout(timeAdd, 1000);//定时器
}



