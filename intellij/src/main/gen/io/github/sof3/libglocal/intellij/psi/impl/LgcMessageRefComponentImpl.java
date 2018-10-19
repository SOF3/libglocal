// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi.impl;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.lang.ASTNode;
import com.intellij.psi.PsiElement;
import com.intellij.psi.PsiElementVisitor;
import com.intellij.psi.util.PsiTreeUtil;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import com.intellij.extapi.psi.ASTWrapperPsiElement;
import io.github.sof3.libglocal.intellij.psi.*;

public class LgcMessageRefComponentImpl extends ASTWrapperPsiElement implements LgcMessageRefComponent {

  public LgcMessageRefComponentImpl(@NotNull ASTNode node) {
    super(node);
  }

  public void accept(@NotNull LgcVisitor visitor) {
    visitor.visitMessageRefComponent(this);
  }

  public void accept(@NotNull PsiElementVisitor visitor) {
    if (visitor instanceof LgcVisitor) accept((LgcVisitor)visitor);
    else super.accept(visitor);
  }

  @Override
  @NotNull
  public List<LgcAttribute> getAttributeList() {
    return PsiTreeUtil.getChildrenOfTypeAsList(this, LgcAttribute.class);
  }

  @Override
  @NotNull
  public PsiElement getCloseBrace() {
    return findNotNullChildByType(CLOSE_BRACE);
  }

  @Override
  @NotNull
  public PsiElement getMessageRefStart() {
    return findNotNullChildByType(MESSAGE_REF_START);
  }

  @Override
  @Nullable
  public PsiElement getDynamic() {
    return findChildByType(MOD_ARG);
  }

  @Override
  @NotNull
  public PsiElement getMessageId() {
    return findNotNullChildByType(IDENTIFIER);
  }

}
